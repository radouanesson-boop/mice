<?php
/**
 * Image sizes for MCB components.
 *
 * Sizes match the design's card ratios so WordPress serves exactly-sized,
 * srcset-enabled images (Core Web Vitals: no layout shift, no oversized
 * downloads). Brikk/Routiz sizes are left untouched.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		// Listing/venue/hotel cards — 13:8 (design board 1A: ≈433×266).
		add_image_size( 'mcb-card', 866, 532, true );

		// Wide feature cards & news — 16:10.
		add_image_size( 'mcb-card-wide', 800, 500, true );

		// Portrait experience cards — 3:4.
		add_image_size( 'mcb-card-tall', 600, 800, true );

		// Full-bleed hero.
		add_image_size( 'mcb-hero', 1920, 1080, true );

		// Partner/press logos row.
		add_image_size( 'mcb-logo', 320, 160, false );
	},
	20
);

/**
 * Expose MCB sizes in media pickers (Elementor lists these automatically).
 */
add_filter(
	'image_size_names_choose',
	function ( $sizes ) {
		return array_merge(
			$sizes,
			array(
				'mcb-card'      => __( 'MCB Card (13:8)', 'mcb' ),
				'mcb-card-wide' => __( 'MCB Card wide (16:10)', 'mcb' ),
				'mcb-card-tall' => __( 'MCB Card tall (3:4)', 'mcb' ),
				'mcb-hero'      => __( 'MCB Hero', 'mcb' ),
			)
		);
	}
);
