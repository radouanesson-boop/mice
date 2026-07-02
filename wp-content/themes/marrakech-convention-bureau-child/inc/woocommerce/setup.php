<?php
/**
 * WooCommerce compatibility (future booking/payments use).
 *
 * Declares support and scopes MCB styles onto Woo templates. Loaded only
 * when WooCommerce is active.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	},
	20
);

add_filter(
	'body_class',
	function ( $classes ) {
		if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
			$classes[] = 'mcb--woo';
		}

		return $classes;
	}
);
