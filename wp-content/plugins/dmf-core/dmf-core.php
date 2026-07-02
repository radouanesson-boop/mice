<?php
/**
 * Plugin Name:       DMF Core — Destination Management Framework
 * Plugin URI:        https://mice.visitmarrakech.com
 * Description:       Enterprise framework for Destination Marketing Organizations, Convention Bureaus and Tourism Boards. Listings, faceted search, maps, events, members, reviews, SEO, REST API — fully modular.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Marrakech Convention Bureau
 * Author URI:        https://mice.visitmarrakech.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dmf
 * Domain Path:       /languages
 *
 * @package DMF
 */

defined( 'ABSPATH' ) || exit;

// -- Constants --------------------------------------------------------------

define( 'DMF_VERSION', '1.0.0' );
define( 'DMF_FILE', __FILE__ );
define( 'DMF_DIR', plugin_dir_path( __FILE__ ) );
define( 'DMF_URL', plugin_dir_url( __FILE__ ) );
define( 'DMF_MIN_PHP', '8.1' );
define( 'DMF_MIN_WP', '6.4' );

// -- Environment guard ------------------------------------------------------

if ( version_compare( PHP_VERSION, DMF_MIN_PHP, '<' ) ) {
	add_action(
		'admin_notices',
		static function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: 1: required PHP version, 2: current PHP version. */
						__( 'DMF Core requires PHP %1$s or newer. This server runs PHP %2$s.', 'dmf' ),
						DMF_MIN_PHP,
						PHP_VERSION
					)
				)
			);
		}
	);
	return;
}

// -- Autoloading ------------------------------------------------------------
// Composer is preferred; the bundled PSR-4 autoloader is the zero-dependency
// fallback so the plugin runs on any host without a build step.

if ( file_exists( DMF_DIR . 'vendor/autoload.php' ) ) {
	require DMF_DIR . 'vendor/autoload.php';
} else {
	require DMF_DIR . 'src/Autoloader.php';
	DMF\Autoloader::register( 'DMF\\', DMF_DIR . 'src/' );
}

// -- Lifecycle --------------------------------------------------------------

register_activation_hook( __FILE__, array( DMF\Modules\Installer\Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( DMF\Modules\Installer\Activator::class, 'deactivate' ) );

/**
 * Access the framework kernel.
 *
 * @return DMF\Plugin
 */
function dmf(): DMF\Plugin {
	return DMF\Plugin::instance();
}

// Boot on plugins_loaded so themes and extension plugins can register
// modules via the `dmf/modules` filter and services via `dmf/container`.
add_action( 'plugins_loaded', static fn() => dmf()->boot(), 5 );
