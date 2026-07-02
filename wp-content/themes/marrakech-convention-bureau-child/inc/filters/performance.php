<?php
/**
 * Performance: lazy loading, responsive images, asset hygiene.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Skip lazy-loading for the first images on archive pages (LCP candidates).
 * WordPress lazy-loads everything by default; the hero/first card should
 * load eagerly with high fetchpriority.
 */
add_filter(
	'wp_get_attachment_image_attributes',
	function ( $attr, $attachment, $size ) {
		if ( 'mcb-hero' === $size ) {
			$attr['loading']       = 'eager';
			$attr['fetchpriority'] = 'high';
		}

		return $attr;
	},
	10,
	3
);

/**
 * Remove emoji scripts — the design uses SVG icons only.
 */
add_action(
	'init',
	function () {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
	}
);

/**
 * Dequeue Brikk assets that the MCB skin fully replaces.
 *
 * Empty by default — populate per-project once you have audited which
 * parent styles are redundant, e.g.:
 *
 *     add_filter( 'mcb/dequeue_styles', fn() => [ 'brikk-google-fonts' ] );
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		foreach ( (array) apply_filters( 'mcb/dequeue_styles', array() ) as $handle ) {
			wp_dequeue_style( $handle );
		}

		foreach ( (array) apply_filters( 'mcb/dequeue_scripts', array() ) as $handle ) {
			wp_dequeue_script( $handle );
		}
	},
	100
);
