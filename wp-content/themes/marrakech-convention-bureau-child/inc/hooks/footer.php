<?php
/**
 * Footer replacement strategy — mirrors inc/hooks/header.php.
 *
 *  1. Elementor Pro "footer" location when a template is assigned.
 *  2. MCB PHP footer (template-parts/footer/site-footer.php).
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the site footer. Called from footer.php.
 */
function mcb_render_footer() {
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) {
		return;
	}

	mcb_part( 'footer/site-footer' );
}
