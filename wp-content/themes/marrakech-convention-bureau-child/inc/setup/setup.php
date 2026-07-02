<?php
/**
 * Theme setup: supports, textdomain, editor niceties.
 *
 * Runs after Brikk's own setup (child theme functions load first, but the
 * hook fires for both), so we only ADD capabilities — never remove Brikk's.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		// Child theme translations (falls back to Brikk's for parent strings).
		load_child_theme_textdomain( 'mcb', MCB_DIR . '/languages' );

		// Semantic HTML5 output everywhere core allows it.
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
		);

		// Native responsive embeds + block editor alignment for news/articles.
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );

		// Let plugins (Yoast, RankMath, Elementor) manage the document title.
		add_theme_support( 'title-tag' );
	},
	20 // After parent theme setup at default priority.
);
