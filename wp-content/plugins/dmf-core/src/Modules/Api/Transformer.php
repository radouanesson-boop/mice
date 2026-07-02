<?php
/**
 * Listing payload transformer.
 *
 * @package DMF
 */

namespace DMF\Modules\Api;

use DMF\Modules\Fields\FieldRegistry;
use DMF\Modules\Listings\PostTypes;

defined( 'ABSPATH' ) || exit;

/**
 * One canonical "card payload" for a listing, shared by every REST endpoint
 * and the frontend live-search renderer.
 */
class Transformer {

	/**
	 * Listing → array payload.
	 *
	 * @param int|\WP_Post $post Listing.
	 * @return array<string, mixed>|null
	 */
	public static function listing( int|\WP_Post $post ): ?array {
		$post = get_post( $post );

		if ( ! $post || PostTypes::LISTING !== $post->post_type ) {
			return null;
		}

		$term = get_the_terms( $post, PostTypes::TYPE_TAXONOMY );
		$geo  = (array) FieldRegistry::value( $post->ID, 'geo', array() );

		$payload = array(
			'id'          => $post->ID,
			'title'       => get_the_title( $post ),
			'url'         => (string) get_permalink( $post ),
			'excerpt'     => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'image'       => (string) get_the_post_thumbnail_url( $post, 'medium_large' ),
			'type'        => is_array( $term ) && $term ? array(
				'slug' => $term[0]->slug,
				'name' => $term[0]->name,
			) : null,
			'rating'      => (float) get_post_meta( $post->ID, '_dmf_rating', true ),
			'reviews'     => (int) get_post_meta( $post->ID, '_dmf_review_count', true ),
			'featured'    => (bool) get_post_meta( $post->ID, '_dmf_featured', true ),
			'verified'    => (bool) get_post_meta( $post->ID, '_dmf_verified', true ),
			'city'        => (string) FieldRegistry::value( $post->ID, 'city' ),
			'district'    => (string) FieldRegistry::value( $post->ID, 'district' ),
			'capacity'    => (int) FieldRegistry::value( $post->ID, 'capacity', 0 ),
			'price_level' => (int) FieldRegistry::value( $post->ID, 'price_level', 0 ),
			'geo'         => isset( $geo['lat'], $geo['lng'] ) ? array(
				'lat' => (float) $geo['lat'],
				'lng' => (float) $geo['lng'],
			) : null,
		);

		/**
		 * Filter the listing card payload.
		 *
		 * @param array<string, mixed> $payload Payload.
		 * @param \WP_Post             $post    Listing post.
		 */
		return (array) apply_filters( 'dmf/api/listing_payload', $payload, $post );
	}
}
