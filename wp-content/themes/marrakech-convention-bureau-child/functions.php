<?php
/**
 * Marrakech Convention Bureau — Brikk child theme bootstrap.
 *
 * This file only defines constants and loads modules from /inc.
 * No business logic lives here: every feature is a small, focused,
 * removable file so the theme stays maintainable and update-safe.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Constants
 * ---------------------------------------------------------------------- */

define( 'MCB_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'MCB_DIR', get_stylesheet_directory() );
define( 'MCB_URI', get_stylesheet_directory_uri() );

/**
 * Post types registered by the Routiz core plugin.
 *
 * Routiz lets the site owner create unlimited "listing types" (venues,
 * hotels, experiences, events...). The slugs below are resolved at runtime
 * in inc/helpers/listing.php — adjust MCB_LISTING_POST_TYPES via the
 * `mcb/listing_post_types` filter if your install differs.
 */
define( 'MCB_LISTING_POST_TYPE_FALLBACK', 'listing' );

/* -------------------------------------------------------------------------
 * Module loader
 * ---------------------------------------------------------------------- */

$mcb_modules = array(
	// Helpers first — everything else may use them.
	'inc/helpers/template.php',
	'inc/helpers/icons.php',
	'inc/helpers/listing.php',
	'inc/helpers/class-mcb-mega-menu-walker.php',

	// Theme setup.
	'inc/setup/setup.php',
	'inc/setup/enqueue.php',
	'inc/setup/menus.php',
	'inc/setup/image-sizes.php',
	'inc/setup/widgets.php',

	// Hooks into Brikk / WordPress rendering.
	'inc/hooks/header.php',
	'inc/hooks/footer.php',
	'inc/hooks/layout.php',

	// Filters.
	'inc/filters/body-class.php',
	'inc/filters/brikk.php',
	'inc/filters/performance.php',

	// Integrations (each file guards its own dependency).
	'inc/elementor/setup.php',
	'inc/woocommerce/setup.php',
);

foreach ( $mcb_modules as $mcb_module ) {
	$mcb_path = MCB_DIR . '/' . $mcb_module;

	if ( file_exists( $mcb_path ) ) {
		require_once $mcb_path;
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf( '[MCB] Missing theme module: %s', $mcb_module ) ); // phpcs:ignore
	}
}
unset( $mcb_modules, $mcb_module, $mcb_path );
