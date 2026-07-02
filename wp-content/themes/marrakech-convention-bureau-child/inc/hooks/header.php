<?php
/**
 * Header replacement strategy.
 *
 * Order of precedence (first available wins):
 *
 *  1. Elementor Pro "header" theme-builder location — client-editable.
 *  2. MCB PHP header (template-parts/header/site-header.php) rendered by
 *     the child theme's header.php override.
 *
 * The child theme ships its own header.php (a standard, update-safe WP
 * override). Brikk's header hooks are additionally neutralised below in a
 * defensive way: every remove_action() checks the callback actually exists
 * so a Brikk update can never fatal this theme.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether an Elementor Pro theme-builder location will render.
 *
 * @param string $location header|footer.
 * @return bool
 */
function mcb_elementor_location_exists( $location ) {
	if ( ! function_exists( 'elementor_theme_do_location' ) || ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
		return false;
	}

	$conditions = \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager();

	return ! empty( $conditions->get_documents_for_location( $location ) );
}

/**
 * Render the site header: Elementor location first, PHP fallback second.
 * Called from header.php.
 */
function mcb_render_header() {
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) {
		return;
	}

	mcb_part( 'header/site-header' );
}

/**
 * Defensively detach Brikk header actions if the parent exposes them.
 *
 * Brikk/Routiz hook names may vary between versions; each removal is
 * guarded, and the list is filterable so an update only requires a
 * one-line project config change — never a core edit.
 */
add_action(
	'wp',
	function () {
		/**
		 * Filter the parent hooks the child theme detaches.
		 *
		 * @param array $detach hook => [ callback, priority ].
		 */
		$detach = apply_filters( 'mcb/detach_parent_hooks', array() );

		foreach ( $detach as $hook => $callbacks ) {
			foreach ( (array) $callbacks as $callback ) {
				if ( has_action( $hook, $callback[0] ) ) {
					remove_action( $hook, $callback[0], isset( $callback[1] ) ? $callback[1] : 10 );
				}
			}
		}
	},
	5
);
