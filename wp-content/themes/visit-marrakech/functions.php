<?php
/**
 * Visit Marrakech theme bootstrap.
 *
 * Presentation only: all destination logic lives in the DMF Core plugin.
 * The theme degrades gracefully (clear admin notice, no fatals) when the
 * framework is inactive.
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

define( 'VM_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'VM_DIR', get_template_directory() );
define( 'VM_URI', get_template_directory_uri() );

// Framework guard.
if ( ! function_exists( 'dmf' ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			echo '<div class="notice notice-error"><p>';
			esc_html_e( 'Visit Marrakech requires the "DMF Core — Destination Management Framework" plugin. Listings, search and forms are disabled until it is activated.', 'vm' );
			echo '</p></div>';
		}
	);
}

foreach ( array(
	'inc/helpers.php',
	'inc/class-mcb-mega-menu-walker.php',
	'inc/setup.php',
	'inc/enqueue.php',
	'inc/menus-widgets.php',
	'inc/customizer.php',
) as $vm_module ) {
	require_once VM_DIR . '/' . $vm_module;
}
unset( $vm_module );
