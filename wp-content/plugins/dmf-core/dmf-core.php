<?php
/**
 * Plugin Name:       DMF Core — Destination Management Framework
 * Plugin URI:        https://github.com/radouanesson-boop/mice
 * Description:       An open, modular, enterprise-grade WordPress framework for Destination Marketing Organizations, Convention Bureaus and Tourism Boards. Unlimited configurable listing types, faceted search, maps, events, members, reviews, favorites, inquiries, SEO — all as independent modules behind a single developer API.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            Marrakech Convention Bureau
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dmf
 * Domain Path:       /languages
 *
 * @package DMF
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

define( 'DMF_VERSION', '0.1.0' );
define( 'DMF_FILE', __FILE__ );
define( 'DMF_DIR', plugin_dir_path( __FILE__ ) );
define( 'DMF_URL', plugin_dir_url( __FILE__ ) );

// ---------------------------------------------------------------------------
// Requirements guard — degrade gracefully, never fatal the site.
// ---------------------------------------------------------------------------

if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
	add_action(
		'admin_notices',
		static function () {
			echo '<div class="notice notice-error"><p>';
			esc_html_e( 'DMF Core requires PHP 8.0 or newer. The plugin is inactive.', 'dmf' );
			echo '</p></div>';
		}
	);
	return;
}

// ---------------------------------------------------------------------------
// Autoloading: Composer when present (dev), bundled PSR-4 fallback (prod).
// ---------------------------------------------------------------------------

if ( file_exists( DMF_DIR . 'vendor/autoload.php' ) ) {
	require DMF_DIR . 'vendor/autoload.php';
} else {
	require DMF_DIR . 'src/Autoloader.php';
	DMF\Autoloader::register( 'DMF\\', DMF_DIR . 'src/' );
}

// Procedural developer API (dmf(), dmf_get_field(), dmf_template()…).
require DMF_DIR . 'src/functions.php';

// ---------------------------------------------------------------------------
// Lifecycle
// ---------------------------------------------------------------------------

register_activation_hook( __FILE__, array( DMF\Plugin::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( DMF\Plugin::class, 'deactivate' ) );

// Boot after plugins are loaded so integrations can filter the module list.
add_action( 'plugins_loaded', static fn() => DMF\Plugin::instance()->boot(), 5 );
