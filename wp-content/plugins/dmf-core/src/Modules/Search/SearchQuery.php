<?php
/**
 * Search query engine.
 *
 * @package DMF
 */

namespace DMF\Modules\Search;

use DMF\Modules\Installer\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Executes searches against the dmf_index table: keyword, type, facets,
 * numeric ranges, geo radius and map bounds — one prepared SQL statement,
 * no meta JOIN explosion. Returns IDs + total; callers hydrate posts.
 */
class SearchQuery {

	/**
	 * Run a search.
	 *
	 * @param array<string, mixed> $args {
	 *     @type string               $keyword   Free-text phrase.
	 *     @type string|string[]     $type      Listing type slug(s).
	 *     @type array<string,mixed> $facets    facet_key => value|value[].
	 *     @type int                 $capacity  Minimum capacity.
	 *     @type int                 $price_max Maximum price level (1–4).
	 *     @type float               $rating    Minimum rating.
	 *     @type bool                $verified  Verified only.
	 *     @type bool                $featured  Featured only.
	 *     @type array{lat: float, lng: float, radius: float} $near Radius search (km).
	 *     @type array{north: float, south: float, east: float, west: float} $bounds Map viewport.
	 *     @type string              $sort      relevance|rating|newest|distance|capacity.
	 *     @type int                 $per_page  Page size (max 100).
	 *     @type int                 $page      1-based page.
	 * }
	 * @return array{ids: int[], total: int, args: array<string, mixed>}
	 */
	public function search( array $args = array() ): array {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'keyword'   => '',
				'type'      => '',
				'facets'    => array(),
				'capacity'  => 0,
				'price_max' => 0,
				'rating'    => 0,
				'verified'  => false,
				'featured'  => false,
				'near'      => array(),
				'bounds'    => array(),
				'sort'      => 'relevance',
				'per_page'  => 12,
				'page'      => 1,
			)
		);

		/**
		 * Filter normalized search arguments.
		 *
		 * @param array<string, mixed> $args Search args.
		 */
		$args = (array) apply_filters( 'dmf/search/args', $args );

		$table  = Schema::table( 'index' );
		$where  = array( "status = 'publish'" );
		$params = array();
		$select = 'listing_id';

		if ( '' !== trim( (string) $args['keyword'] ) ) {
			$words = preg_split( '/\s+/u', mb_strtolower( trim( (string) $args['keyword'] ) ) ) ?: array();
			foreach ( array_slice( $words, 0, 8 ) as $word ) {
				$where[]  = 'search_text LIKE %s';
				$params[] = '%' . $wpdb->esc_like( $word ) . '%';
			}
		}

		$types = array_filter( array_map( 'sanitize_title', (array) $args['type'] ) );
		if ( $types ) {
			$where[] = 'listing_type IN (' . implode( ',', array_fill( 0, count( $types ), '%s' ) ) . ')';
			array_push( $params, ...$types );
		}

		foreach ( (array) $args['facets'] as $facet => $values ) {
			$facet   = sanitize_key( (string) $facet );
			$values  = array_filter( array_map( static fn( $v ) => sanitize_title( (string) $v ), (array) $values ) );
			if ( ! $values ) {
				continue;
			}
			// Multiple values of one facet are OR; facets combine as AND.
			$clauses = array();
			foreach ( $values as $value ) {
				$clauses[] = 'facets LIKE %s';
				$params[]  = '%' . $wpdb->esc_like( " {$facet}:{$value} " ) . '%';
			}
			$where[] = '(' . implode( ' OR ', $clauses ) . ')';
		}

		if ( (int) $args['capacity'] > 0 ) {
			$where[]  = 'capacity >= %d';
			$params[] = (int) $args['capacity'];
		}
		if ( (int) $args['price_max'] > 0 ) {
			$where[]  = '(price_level = 0 OR price_level <= %d)';
			$params[] = (int) $args['price_max'];
		}
		if ( (float) $args['rating'] > 0 ) {
			$where[]  = 'rating >= %f';
			$params[] = (float) $args['rating'];
		}
		if ( $args['verified'] ) {
			$where[] = 'verified = 1';
		}
		if ( $args['featured'] ) {
			$where[] = 'featured = 1';
		}

		$near = (array) $args['near'];
		if ( isset( $near['lat'], $near['lng'] ) && is_numeric( $near['lat'] ) && is_numeric( $near['lng'] ) ) {
			$radius = isset( $near['radius'] ) && is_numeric( $near['radius'] ) ? min( 500, max( 0.1, (float) $near['radius'] ) ) : 25.0;

			// Haversine distance in km, exposed for distance sorting.
			$select  .= $wpdb->prepare(
				', ( 6371 * ACOS( LEAST( 1, COS(RADIANS(%f)) * COS(RADIANS(lat)) * COS(RADIANS(lng) - RADIANS(%f)) + SIN(RADIANS(%f)) * SIN(RADIANS(lat)) ) ) ) AS distance',
				(float) $near['lat'],
				(float) $near['lng'],
				(float) $near['lat']
			);
			$where[]  = 'lat IS NOT NULL AND lng IS NOT NULL';
			$having   = $wpdb->prepare( 'HAVING distance <= %f', $radius );
		} else {
			$having = '';
		}

		$bounds = (array) $args['bounds'];
		if ( isset( $bounds['north'], $bounds['south'], $bounds['east'], $bounds['west'] ) ) {
			$where[]  = 'lat BETWEEN %f AND %f';
			$params[] = (float) $bounds['south'];
			$params[] = (float) $bounds['north'];
			$where[]  = 'lng BETWEEN %f AND %f';
			$params[] = (float) $bounds['west'];
			$params[] = (float) $bounds['east'];
		}

		$order = match ( (string) $args['sort'] ) {
			'rating'   => 'rating DESC, review_count DESC',
			'newest'   => 'listing_id DESC',
			'capacity' => 'capacity DESC',
			'distance' => $having ? 'distance ASC' : 'featured DESC, rating DESC',
			default    => 'featured DESC, rating DESC, review_count DESC',
		};

		$per_page = max( 1, min( 100, (int) $args['per_page'] ) );
		$offset   = ( max( 1, (int) $args['page'] ) - 1 ) * $per_page;

		$where_sql = implode( ' AND ', $where );

		$sql = "SELECT SQL_CALC_FOUND_ROWS {$select} FROM {$table} WHERE {$where_sql} {$having} ORDER BY {$order} LIMIT %d OFFSET %d";

		$params[] = $per_page;
		$params[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- clauses built with prepare()/placeholders above.
		$ids   = array_map( 'intval', $wpdb->get_col( $wpdb->prepare( $sql, $params ) ) );
		$total = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		/**
		 * Fires after a search executes (analytics, saved-search matching).
		 *
		 * @param array<string, mixed> $args  Search args.
		 * @param int[]                $ids   Result IDs (page).
		 * @param int                  $total Total results.
		 */
		do_action( 'dmf/search/executed', $args, $ids, $total );

		return array(
			'ids'   => $ids,
			'total' => $total,
			'args'  => $args,
		);
	}

	/**
	 * Autocomplete suggestions: listing titles + taxonomy terms.
	 *
	 * @param string $phrase Partial phrase.
	 * @param int    $limit  Max suggestions.
	 * @return array<int, array{label: string, type: string, url: string}>
	 */
	public function suggest( string $phrase, int $limit = 8 ): array {
		$phrase = trim( $phrase );
		if ( mb_strlen( $phrase ) < 2 ) {
			return array();
		}

		$suggestions = array();

		$posts = get_posts(
			array(
				'post_type'      => \DMF\Modules\Listings\PostTypes::LISTING,
				'post_status'    => 'publish',
				's'              => $phrase,
				'posts_per_page' => $limit,
				'orderby'        => 'relevance',
			)
		);

		foreach ( $posts as $post ) {
			$suggestions[] = array(
				'label' => get_the_title( $post ),
				'type'  => 'listing',
				'url'   => (string) get_permalink( $post ),
			);
		}

		$terms = get_terms(
			array(
				'taxonomy'   => array( \DMF\Modules\Listings\PostTypes::TYPE_TAXONOMY, \DMF\Modules\Listings\PostTypes::CATEGORY ),
				'name__like' => $phrase,
				'number'     => max( 0, $limit - count( $suggestions ) ),
				'hide_empty' => true,
			)
		);

		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$suggestions[] = array(
					'label' => $term->name,
					'type'  => 'category',
					'url'   => (string) get_term_link( $term ),
				);
			}
		}

		return $suggestions;
	}
}
