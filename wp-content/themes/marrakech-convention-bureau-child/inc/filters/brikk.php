<?php
/**
 * Filters targeting Brikk / Routiz output.
 *
 * Presentation-only adjustments. Functional behaviour (search logic,
 * filtering, booking, dashboard) is deliberately untouched.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Uniform excerpt length for cards.
 */
add_filter(
	'excerpt_length',
	function ( $length ) {
		return is_admin() ? $length : 18;
	},
	20
);

add_filter(
	'excerpt_more',
	function ( $more ) {
		return is_admin() ? $more : '…';
	},
	20
);

/**
 * Sensible grid density on listing archives (Brikk's query settings still
 * win if it sets posts_per_page explicitly at a later priority).
 */
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->is_post_type_archive( mcb_listing_post_types() ) ) {
			$query->set( 'posts_per_page', (int) apply_filters( 'mcb/archive_per_page', 12 ) );
		}
	},
	9 // Before Brikk/Routiz (priority 10+) so their settings take precedence.
);

/**
 * Route Brikk/Routiz archive & search wrappers through MCB classes when the
 * parent exposes wrapper filters. Guarded: applies only if the filter fires.
 */
add_filter(
	'routiz_archive_wrapper_class',
	function ( $class ) {
		return trim( $class . ' mcb-archive' );
	}
);

add_filter(
	'brikk_container_class',
	function ( $class ) {
		return trim( $class . ' mcb-container' );
	}
);
