<?php
/**
 * Search module — live search, filters, facets, radius.
 *
 * @package DMF
 */

namespace DMF\Modules\Search;

use DMF\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * REST-first search engine.
 *
 *   GET /wp-json/dmf/v1/search
 *     ?q=palais                 keyword (title + content)
 *     &type=dmf_venue,dmf_hotel post types (default: all listing types)
 *     &dmf_category=congress    any registered taxonomy => term slugs
 *     &meta[capacity][min]=500  numeric range filters on searchable fields
 *     &near=31.62,-7.99&radius=25   km radius around lat,lng
 *     &orderby=relevance|date|title|distance
 *     &page=1&per_page=12
 *     &facets=1                 include term counts for filter UIs
 *
 * The endpoint is public (read-only, published content only) and returns
 * lean card payloads — the front end renders instantly without a page load.
 */
final class SearchModule extends AbstractModule {

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'routes' ) );
	}

	public function routes(): void {
		register_rest_route(
			'dmf/v1',
			'/search',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'handle' ),
				'permission_callback' => '__return_true', // Public, published-only reads.
				'args'                => array(
					'q'        => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
					'type'     => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
					'near'     => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
					'radius'   => array( 'type' => 'number', 'default' => 25 ),
					'orderby'  => array( 'type' => 'string', 'default' => 'relevance' ),
					'page'     => array( 'type' => 'integer', 'default' => 1, 'minimum' => 1 ),
					'per_page' => array( 'type' => 'integer', 'default' => 12, 'minimum' => 1, 'maximum' => 50 ),
					'facets'   => array( 'type' => 'boolean', 'default' => false ),
				),
			)
		);
	}

	public function handle( \WP_REST_Request $request ): \WP_REST_Response {
		$registry   = dmf()->module( 'listings' )->registry();
		$post_types = $registry->post_types();

		// Scope to requested types (validated against registered ones).
		if ( $request['type'] ) {
			$wanted     = array_map( 'sanitize_key', explode( ',', (string) $request['type'] ) );
			$post_types = array_values( array_intersect( $post_types, $wanted ) ) ?: $post_types;
		}

		$args = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => (int) $request['per_page'],
			'paged'          => (int) $request['page'],
		);

		if ( $request['q'] ) {
			$args['s'] = (string) $request['q'];
		}

		// Taxonomy filters: every registered taxonomy is a query param.
		$tax_query = array();
		foreach ( get_object_taxonomies( $post_types ) as $taxonomy ) {
			$param = $request->get_param( $taxonomy );

			if ( $param ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => array_map( 'sanitize_title', explode( ',', (string) $param ) ),
				);
			}
		}
		if ( $tax_query ) {
			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery
		}

		// Meta range filters on searchable fields: meta[capacity][min]=500.
		$meta_query = array();
		foreach ( (array) $request->get_param( 'meta' ) as $key => $range ) {
			$key   = sanitize_key( $key );
			$range = (array) $range;

			if ( isset( $range['min'] ) && is_numeric( $range['min'] ) ) {
				$meta_query[] = array( 'key' => "_dmf_{$key}", 'value' => (float) $range['min'], 'compare' => '>=', 'type' => 'NUMERIC' );
			}
			if ( isset( $range['max'] ) && is_numeric( $range['max'] ) ) {
				$meta_query[] = array( 'key' => "_dmf_{$key}", 'value' => (float) $range['max'], 'compare' => '<=', 'type' => 'NUMERIC' );
			}
			if ( isset( $range['is'] ) ) {
				$meta_query[] = array( 'key' => "_dmf_{$key}", 'value' => sanitize_text_field( (string) $range['is'] ) );
			}
		}
		if ( $meta_query ) {
			$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery
		}

		$sort = (string) $request['orderby'];
		if ( 'date' === $sort ) {
			$args['orderby'] = 'date';
		} elseif ( 'title' === $sort ) {
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
		}

		/**
		 * Filter the search WP_Query args.
		 *
		 * @param array            $args    Query args.
		 * @param \WP_REST_Request $request Request.
		 */
		$query = new \WP_Query( apply_filters( 'dmf/search_query_args', $args, $request ) );

		$items = array_map( array( $this, 'format_item' ), $query->posts );

		// Radius filter + distance sort (post-query haversine — fine at
		// destination scale; swap for a geo table via the filter above
		// when a deployment exceeds ~10k listings).
		$near = $this->parse_near( (string) $request['near'] );
		if ( $near ) {
			$radius = max( 0.1, (float) $request['radius'] );
			$items  = array_values(
				array_filter(
					array_map(
						function ( array $item ) use ( $near ) {
							if ( ! $item['geo'] ) {
								return null;
							}
							$item['distance_km'] = round( $this->haversine( $near, $item['geo'] ), 2 );
							return $item;
						},
						$items
					),
					static fn( $item ) => $item && $item['distance_km'] <= $radius
				)
			);

			if ( 'distance' === $sort ) {
				usort( $items, static fn( $a, $b ) => $a['distance_km'] <=> $b['distance_km'] );
			}
		}

		$payload = array(
			'items'       => $items,
			'total'       => (int) $query->found_posts,
			'total_pages' => (int) $query->max_num_pages,
			'page'        => (int) $request['page'],
		);

		if ( $request['facets'] ) {
			$payload['facets'] = $this->facets( $post_types );
		}

		return rest_ensure_response( $payload );
	}

	/**
	 * Lean card payload for one result.
	 *
	 * @return array<string, mixed>
	 */
	private function format_item( \WP_Post $post ): array {
		$geo = get_post_meta( $post->ID, '_dmf_geo', true );

		$item = array(
			'id'        => $post->ID,
			'type'      => $post->post_type,
			'kind'      => dmf()->module( 'listings' )->registry()->get( $post->post_type )?->kind ?? 'generic',
			'title'     => get_the_title( $post ),
			'url'       => get_permalink( $post ),
			'excerpt'   => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'image'     => get_the_post_thumbnail_url( $post, 'medium_large' ) ?: null,
			'terms'     => array_map(
				static fn( \WP_Term $t ) => array( 'slug' => $t->slug, 'name' => $t->name, 'taxonomy' => $t->taxonomy ),
				wp_get_post_terms( $post->ID, get_object_taxonomies( $post->post_type ) ) ?: array()
			),
			'fields'    => dmf_get_card_fields( $post->ID ),
			'geo'       => ( is_array( $geo ) && '' !== ( $geo['lat'] ?? '' ) )
				? array( 'lat' => (float) $geo['lat'], 'lng' => (float) $geo['lng'] )
				: null,
		);

		/**
		 * Filter a search result payload.
		 *
		 * @param array    $item Payload.
		 * @param \WP_Post $post Source post.
		 */
		return apply_filters( 'dmf/search_item', $item, $post );
	}

	/**
	 * Term counts per taxonomy for faceted filter UIs.
	 *
	 * @param string[] $post_types Scoped post types.
	 * @return array<string, array<int, array{slug: string, name: string, count: int}>>
	 */
	private function facets( array $post_types ): array {
		$facets = array();

		foreach ( get_object_taxonomies( $post_types, 'objects' ) as $taxonomy ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy->name,
					'hide_empty' => true,
					'number'     => 50,
				)
			);

			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			}

			$facets[ $taxonomy->name ] = array_map(
				static fn( \WP_Term $t ) => array( 'slug' => $t->slug, 'name' => $t->name, 'count' => (int) $t->count ),
				$terms
			);
		}

		return $facets;
	}

	/**
	 * @return array{lat: float, lng: float}|null
	 */
	private function parse_near( string $near ): ?array {
		if ( ! preg_match( '/^(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)$/', $near, $m ) ) {
			return null;
		}

		return array( 'lat' => (float) $m[1], 'lng' => (float) $m[2] );
	}

	/**
	 * Great-circle distance in km.
	 *
	 * @param array{lat: float, lng: float} $a Point A.
	 * @param array{lat: float, lng: float} $b Point B.
	 */
	private function haversine( array $a, array $b ): float {
		$earth = 6371.0;
		$dlat  = deg2rad( $b['lat'] - $a['lat'] );
		$dlng  = deg2rad( $b['lng'] - $a['lng'] );

		$h = sin( $dlat / 2 ) ** 2
			+ cos( deg2rad( $a['lat'] ) ) * cos( deg2rad( $b['lat'] ) ) * sin( $dlng / 2 ) ** 2;

		return 2 * $earth * asin( min( 1.0, sqrt( $h ) ) );
	}
}
