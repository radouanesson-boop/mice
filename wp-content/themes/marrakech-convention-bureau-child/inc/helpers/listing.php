<?php
/**
 * Routiz / Brikk listing data helpers.
 *
 * Brikk's functionality comes from the Routiz core plugin, which lets the
 * site owner define unlimited listing types (Venues, Hotels, Experiences,
 * Events…). Because those types are user-configurable, nothing here
 * hardcodes a post type: everything is resolved at runtime and filterable.
 *
 * All helpers degrade gracefully when Routiz is inactive, so the theme
 * never fatals during plugin updates or staging imports.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the Routiz core plugin is active.
 *
 * @return bool
 */
function mcb_routiz_active() {
	return class_exists( 'Routiz' ) || function_exists( 'routiz' ) || defined( 'ROUTIZ_VERSION' );
}

/**
 * Post types that behave as "listings" on this install.
 *
 * Detected from registered post types, overridable via filter:
 *
 *     add_filter( 'mcb/listing_post_types', fn() => [ 'listing' ] );
 *
 * @return string[]
 */
function mcb_listing_post_types() {
	static $types = null;

	if ( null === $types ) {
		$candidates = array( MCB_LISTING_POST_TYPE_FALLBACK, 'routiz_listing', 'ulisting' );
		$types      = array_values( array_filter( $candidates, 'post_type_exists' ) );

		if ( empty( $types ) ) {
			$types = array( MCB_LISTING_POST_TYPE_FALLBACK );
		}
	}

	/**
	 * Filter the post types the theme treats as listings.
	 *
	 * @param string[] $types Post type slugs.
	 */
	return apply_filters( 'mcb/listing_post_types', $types );
}

/**
 * Whether a post is a Routiz listing.
 *
 * @param int|WP_Post|null $post Post.
 * @return bool
 */
function mcb_is_listing( $post = null ) {
	return in_array( get_post_type( $post ), mcb_listing_post_types(), true );
}

/**
 * The listing "kind" used to pick a card design: venue|hotel|experience|event.
 *
 * Resolution order:
 *  1. `mcb/listing_kind` filter (project-level mapping).
 *  2. Routiz listing-type taxonomy term slug, when one matches a known kind.
 *  3. 'listing' as neutral fallback.
 *
 * @param int|WP_Post|null $post Post.
 * @return string
 */
function mcb_listing_kind( $post = null ) {
	$post  = get_post( $post );
	$kinds = array( 'venue', 'hotel', 'experience', 'event' );
	$found = 'listing';

	if ( $post ) {
		foreach ( get_object_taxonomies( $post->post_type ) as $taxonomy ) {
			$terms = get_the_terms( $post, $taxonomy );

			if ( ! is_array( $terms ) ) {
				continue;
			}

			foreach ( $terms as $term ) {
				foreach ( $kinds as $kind ) {
					if ( false !== strpos( $term->slug, $kind ) ) {
						$found = $kind;
						break 3;
					}
				}
			}
		}
	}

	/**
	 * Filter the resolved listing kind for a post.
	 *
	 * @param string       $found Resolved kind.
	 * @param WP_Post|null $post  Post object.
	 */
	return apply_filters( 'mcb/listing_kind', $found, $post );
}

/**
 * Meta value helper with Routiz-aware fallbacks.
 *
 * Routiz stores listing fields as post meta. Field keys are configured in
 * the Routiz field builder — map your keys once via the filter:
 *
 *     add_filter( 'mcb/listing_meta_map', fn( $map ) => $map + [
 *         'capacity' => 'routiz_field_capacity',
 *     ] );
 *
 * @param string           $key  Logical key (capacity, price, location…).
 * @param int|WP_Post|null $post Post.
 * @return mixed
 */
function mcb_listing_meta( $key, $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return '';
	}

	/**
	 * Map logical card fields to actual Routiz meta keys.
	 *
	 * @param array $map logical-key => meta-key.
	 */
	$map      = apply_filters( 'mcb/listing_meta_map', array() );
	$meta_key = isset( $map[ $key ] ) ? $map[ $key ] : $key;

	return get_post_meta( $post->ID, $meta_key, true );
}

/**
 * Average rating for a listing (Routiz reviews), 0 when unavailable.
 *
 * @param int|WP_Post|null $post Post.
 * @return float
 */
function mcb_listing_rating( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return 0.0;
	}

	// Common meta keys used by directory review systems, first hit wins.
	foreach ( apply_filters( 'mcb/rating_meta_keys', array( 'routiz_rating', '_rating', 'rating', 'average_rating' ) ) as $key ) {
		$value = get_post_meta( $post->ID, $key, true );

		if ( '' !== $value && is_numeric( $value ) ) {
			return round( (float) $value, 1 );
		}
	}

	return 0.0;
}

/**
 * Primary category term for a listing (used for card badges).
 *
 * @param int|WP_Post|null $post Post.
 * @return WP_Term|null
 */
function mcb_listing_primary_term( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return null;
	}

	foreach ( get_object_taxonomies( $post->post_type, 'objects' ) as $taxonomy ) {
		if ( ! $taxonomy->public || ! $taxonomy->hierarchical ) {
			continue;
		}

		$terms = get_the_terms( $post, $taxonomy->name );

		if ( is_array( $terms ) && ! empty( $terms ) ) {
			return $terms[0];
		}
	}

	return null;
}

/**
 * Query listings for grids/sections (used by Elementor widgets & templates).
 *
 * @param array $args {
 *     @type int    $count    Number of listings. Default 6.
 *     @type string $taxonomy Optional taxonomy to filter by.
 *     @type array  $terms    Term slugs for $taxonomy.
 *     @type string $orderby  date|title|rand|menu_order. Default date.
 * }
 * @return WP_Query
 */
function mcb_listing_query( array $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'count'    => 6,
			'taxonomy' => '',
			'terms'    => array(),
			'orderby'  => 'date',
		)
	);

	$query_args = array(
		'post_type'           => mcb_listing_post_types(),
		'posts_per_page'      => (int) $args['count'],
		'post_status'         => 'publish',
		'orderby'             => $args['orderby'],
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	if ( $args['taxonomy'] && ! empty( $args['terms'] ) ) {
		$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'taxonomy' => $args['taxonomy'],
				'field'    => 'slug',
				'terms'    => (array) $args['terms'],
			),
		);
	}

	/**
	 * Filter the WP_Query args used for MCB listing grids.
	 *
	 * @param array $query_args WP_Query args.
	 * @param array $args       Original helper args.
	 */
	return new WP_Query( apply_filters( 'mcb/listing_query_args', $query_args, $args ) );
}
