<?php
/**
 * Listing content model registration.
 *
 * @package DMF
 */

namespace DMF\Modules\Listings;

use DMF\Support\Options;

defined( 'ABSPATH' ) || exit;

/**
 * One canonical `dmf_listing` post type carries every listing kind; the
 * `dmf_listing_type` taxonomy (mirrored from the type registry) decides
 * which field groups, facets and card template apply. One CPT keeps
 * queries, permalinks, counts and REST simple no matter how many types a
 * destination creates.
 */
class PostTypes {

	public const LISTING       = 'dmf_listing';
	public const TYPE_TAXONOMY = 'dmf_listing_type';
	public const CATEGORY      = 'dmf_category';
	public const TAG           = 'dmf_tag';

	/**
	 * Constructor.
	 *
	 * @param Options $options Settings accessor.
	 */
	public function __construct( private Options $options ) {}

	/**
	 * Register the post type and taxonomies.
	 */
	public function register(): void {
		$archive_slug = (string) $this->options->get( 'listings.archive_slug', 'listings' );

		register_post_type(
			self::LISTING,
			array(
				'labels'          => array(
					'name'          => __( 'Listings', 'dmf' ),
					'singular_name' => __( 'Listing', 'dmf' ),
					'add_new_item'  => __( 'Add New Listing', 'dmf' ),
					'edit_item'     => __( 'Edit Listing', 'dmf' ),
					'search_items'  => __( 'Search Listings', 'dmf' ),
					'not_found'     => __( 'No listings found.', 'dmf' ),
				),
				'public'          => true,
				'menu_icon'       => 'dashicons-location-alt',
				'menu_position'   => 25,
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
				'has_archive'     => $archive_slug,
				'rewrite'         => array(
					'slug'       => 'listing',
					'with_front' => false,
				),
				'capability_type' => 'dmf_listing',
				'map_meta_cap'    => true,
				'show_in_rest'    => true,
				'rest_base'       => 'dmf-listings',
			)
		);

		register_taxonomy(
			self::TYPE_TAXONOMY,
			self::LISTING,
			array(
				'labels'            => array(
					'name'          => __( 'Listing Types', 'dmf' ),
					'singular_name' => __( 'Listing Type', 'dmf' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => $archive_slug,
					'with_front' => false,
				),
				// Types are managed in DMF → Listing Types, not the term UI.
				'capabilities'      => array(
					'manage_terms' => 'manage_dmf',
					'edit_terms'   => 'manage_dmf',
					'delete_terms' => 'manage_dmf',
					'assign_terms' => 'edit_dmf_listings',
				),
			)
		);

		register_taxonomy(
			self::CATEGORY,
			self::LISTING,
			array(
				'labels'            => array(
					'name'          => __( 'Listing Categories', 'dmf' ),
					'singular_name' => __( 'Listing Category', 'dmf' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'listing-category', 'with_front' => false ),
			)
		);

		register_taxonomy(
			self::TAG,
			self::LISTING,
			array(
				'labels'       => array(
					'name'          => __( 'Listing Tags', 'dmf' ),
					'singular_name' => __( 'Listing Tag', 'dmf' ),
				),
				'public'       => true,
				'hierarchical' => false,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => 'listing-tag', 'with_front' => false ),
			)
		);

		register_post_meta(
			self::LISTING,
			'_dmf_featured',
			array(
				'type'              => 'boolean',
				'single'            => true,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'auth_callback'     => static fn() => current_user_can( 'manage_dmf' ),
			)
		);

		register_post_meta(
			self::LISTING,
			'_dmf_verified',
			array(
				'type'              => 'boolean',
				'single'            => true,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'auth_callback'     => static fn() => current_user_can( 'manage_dmf' ),
			)
		);
	}

	/**
	 * Primary listing type slug for a listing.
	 *
	 * @param int $post_id Listing ID.
	 */
	public static function listing_type_slug( int $post_id ): string {
		$terms = get_the_terms( $post_id, self::TYPE_TAXONOMY );

		if ( ! is_array( $terms ) || ! $terms ) {
			return '';
		}

		return (string) $terms[0]->slug;
	}
}
