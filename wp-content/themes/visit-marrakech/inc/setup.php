<?php
/**
 * Theme setup: supports, image sizes, editor.
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		load_theme_textdomain( 'vm', VM_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array( 'height' => 96, 'flex-width' => true ) );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/main.css' );

		// Design card ratios (board 1A).
		add_image_size( 'mcb-card', 866, 532, true );        // 13:8 venue/hotel cards.
		add_image_size( 'mcb-card-wide', 800, 500, true );   // 16:10 news/events.
		add_image_size( 'mcb-card-tall', 600, 800, true );   // 3:4 experiences.
		add_image_size( 'mcb-hero', 1920, 1080, true );
		add_image_size( 'mcb-logo', 320, 160, false );
	}
);

add_filter(
	'image_size_names_choose',
	static fn( array $sizes ): array => array_merge(
		$sizes,
		array(
			'mcb-card'      => __( 'Card (13:8)', 'vm' ),
			'mcb-card-wide' => __( 'Card wide (16:10)', 'vm' ),
			'mcb-card-tall' => __( 'Card tall (3:4)', 'vm' ),
			'mcb-hero'      => __( 'Hero', 'vm' ),
		)
	)
);

// Body classes used as styling scopes.
add_filter(
	'body_class',
	static function ( array $classes ): array {
		$classes[] = 'mcb';

		if ( is_front_page() ) {
			$classes[] = 'mcb--home';
		}

		if ( mcb_is_listing() && is_singular() ) {
			$classes[] = 'mcb--single-listing';
			$classes[] = 'mcb--kind-' . sanitize_html_class( mcb_listing_kind() );
		}

		return $classes;
	}
);
