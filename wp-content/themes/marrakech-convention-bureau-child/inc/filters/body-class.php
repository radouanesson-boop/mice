<?php
/**
 * Body classes used by the MCB stylesheet as styling scopes.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'body_class',
	function ( $classes ) {
		$classes[] = 'mcb';

		if ( is_front_page() ) {
			$classes[] = 'mcb--home';
		}

		if ( is_singular( mcb_listing_post_types() ) ) {
			$classes[] = 'mcb--single-listing';
			$classes[] = 'mcb--kind-' . sanitize_html_class( mcb_listing_kind() );
		}

		if ( is_post_type_archive( mcb_listing_post_types() ) || is_search() ) {
			$classes[] = 'mcb--archive-listing';
		}

		return $classes;
	}
);
