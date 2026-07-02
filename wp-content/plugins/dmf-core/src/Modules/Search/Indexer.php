<?php
/**
 * Listing search indexer.
 *
 * @package DMF
 */

namespace DMF\Modules\Search;

use DMF\Modules\Fields\FieldRegistry;
use DMF\Modules\Installer\Schema;
use DMF\Modules\Listings\PostTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Denormalizes each listing into one `dmf_index` row: geo point, numeric
 * ranges, rating, flags, facet tokens and a keyword blob. Every search the
 * frontend runs — live search, faceted filtering, radius, map bounds — hits
 * this single indexed table instead of unbounded meta JOINs.
 *
 * Facet tokens are stored as a space-padded token list, e.g.
 * " district:medina amenities:wifi amenities:pool " so any facet can be
 * matched with an index-friendly LIKE '% key:value %'.
 */
class Indexer {

	/**
	 * Constructor.
	 *
	 * @param FieldRegistry $fields Field registry.
	 */
	public function __construct( private FieldRegistry $fields ) {}

	/**
	 * (Re)index one listing.
	 *
	 * @param int $post_id Listing ID.
	 */
	public function index( int $post_id ): void {
		global $wpdb;

		$post = get_post( $post_id );

		if ( ! $post || PostTypes::LISTING !== $post->post_type ) {
			return;
		}

		if ( 'publish' !== $post->post_status ) {
			$this->remove( $post_id );
			return;
		}

		$type_slug = PostTypes::listing_type_slug( $post_id );
		$values    = array();

		foreach ( array_keys( $this->fields->fields_for_type( $type_slug ) ) as $key ) {
			$values[ $key ] = FieldRegistry::value( $post_id, $key );
		}

		$geo = (array) ( $values['geo'] ?? array() );

		$tokens = $this->facet_tokens( $values );
		$text   = $this->search_text( $post, $values );

		/**
		 * Filter the index row before it is written.
		 *
		 * @param array<string, mixed> $row     Column => value.
		 * @param int                  $post_id Listing ID.
		 * @param array<string, mixed> $values  Field values.
		 */
		$row = (array) apply_filters(
			'dmf/search/index_row',
			array(
				'listing_id'   => $post_id,
				'listing_type' => $type_slug,
				'status'       => $post->post_status,
				'lat'          => isset( $geo['lat'] ) ? (float) $geo['lat'] : null,
				'lng'          => isset( $geo['lng'] ) ? (float) $geo['lng'] : null,
				'city'         => sanitize_text_field( (string) ( $values['city'] ?? '' ) ),
				'capacity'     => absint( $values['capacity'] ?? 0 ),
				'price_level'  => absint( $values['price_level'] ?? 0 ),
				'rating'       => (float) get_post_meta( $post_id, '_dmf_rating', true ),
				'review_count' => absint( get_post_meta( $post_id, '_dmf_review_count', true ) ),
				'featured'     => get_post_meta( $post_id, '_dmf_featured', true ) ? 1 : 0,
				'verified'     => get_post_meta( $post_id, '_dmf_verified', true ) ? 1 : 0,
				'facets'       => $tokens,
				'search_text'  => $text,
				'updated_at'   => current_time( 'mysql', true ),
			),
			$post_id,
			$values
		);

		$wpdb->replace( Schema::table( 'index' ), $row ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- purpose-built index table.
	}

	/**
	 * Drop a listing from the index.
	 *
	 * @param int $post_id Listing ID.
	 */
	public function remove( int $post_id ): void {
		global $wpdb;
		$wpdb->delete( Schema::table( 'index' ), array( 'listing_id' => $post_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Rebuild the whole index in batches (cron / CLI).
	 *
	 * @return int Number of listings indexed.
	 */
	public function rebuild(): int {
		$paged = 1;
		$count = 0;

		do {
			$ids = get_posts(
				array(
					'post_type'      => PostTypes::LISTING,
					'post_status'    => 'publish',
					'posts_per_page' => 100,
					'paged'          => $paged,
					'fields'         => 'ids',
					'orderby'        => 'ID',
					'order'          => 'ASC',
				)
			);

			foreach ( $ids as $id ) {
				$this->index( (int) $id );
				++$count;
			}

			++$paged;
		} while ( count( $ids ) === 100 );

		return $count;
	}

	/**
	 * Build the facet token list from field values.
	 *
	 * @param array<string, mixed> $values Field values keyed by field key.
	 */
	private function facet_tokens( array $values ): string {
		$tokens = array();

		foreach ( $values as $key => $value ) {
			if ( is_array( $value ) ) {
				// Multicheck-style values: one token per option.
				foreach ( $value as $option ) {
					if ( is_scalar( $option ) && '' !== (string) $option ) {
						$tokens[] = $key . ':' . sanitize_title( (string) $option );
					}
				}
			} elseif ( is_scalar( $value ) && '' !== (string) $value && strlen( (string) $value ) <= 64 ) {
				$tokens[] = $key . ':' . sanitize_title( (string) $value );
			}
		}

		return $tokens ? ' ' . implode( ' ', array_unique( $tokens ) ) . ' ' : '';
	}

	/**
	 * Build the keyword blob.
	 *
	 * @param \WP_Post             $post   Listing post.
	 * @param array<string, mixed> $values Field values.
	 */
	private function search_text( \WP_Post $post, array $values ): string {
		$parts = array( $post->post_title, $post->post_excerpt, wp_strip_all_tags( $post->post_content ) );

		foreach ( $values as $value ) {
			if ( is_scalar( $value ) ) {
				$parts[] = (string) $value;
			} elseif ( is_array( $value ) ) {
				$parts[] = implode( ' ', array_filter( $value, 'is_scalar' ) );
			}
		}

		foreach ( array( PostTypes::TYPE_TAXONOMY, PostTypes::CATEGORY, PostTypes::TAG ) as $taxonomy ) {
			$terms = get_the_terms( $post->ID, $taxonomy );
			if ( is_array( $terms ) ) {
				$parts[] = implode( ' ', wp_list_pluck( $terms, 'name' ) );
			}
		}

		return mb_strtolower( trim( preg_replace( '/\s+/u', ' ', implode( ' ', array_filter( $parts ) ) ) ?? '' ) );
	}
}
