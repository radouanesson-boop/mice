<?php
/**
 * Menu locations owned by the child theme.
 *
 * Brikk registers its own locations (kept intact for its templates). These
 * additional locations power the MCB header, mega menu, mobile drawer and
 * footer columns.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		register_nav_menus(
			array(
				'mcb_primary'      => __( 'MCB — Primary (desktop, supports mega menu)', 'mcb' ),
				'mcb_mobile'       => __( 'MCB — Mobile drawer (falls back to Primary)', 'mcb' ),
				'mcb_topbar'       => __( 'MCB — Top bar quick links', 'mcb' ),
				'mcb_footer_1'     => __( 'MCB — Footer column 1', 'mcb' ),
				'mcb_footer_2'     => __( 'MCB — Footer column 2', 'mcb' ),
				'mcb_footer_3'     => __( 'MCB — Footer column 3', 'mcb' ),
				'mcb_footer_legal' => __( 'MCB — Footer legal bar', 'mcb' ),
			)
		);
	},
	20
);
