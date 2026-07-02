<?php
/**
 * Activation / deactivation lifecycle.
 *
 * @package DMF
 */

namespace DMF\Modules\Installer;

use DMF\Modules\ListingTypes\Seeder;

defined( 'ABSPATH' ) || exit;

/**
 * Runs on activation: schema, roles, seed content, cron, rewrites.
 * Deactivation only unschedules cron — data is never destroyed outside
 * uninstall.php.
 */
class Activator {

	/**
	 * Plugin activation.
	 */
	public static function activate(): void {
		Schema::migrate();
		Roles::install();
		Seeder::seed();

		if ( ! wp_next_scheduled( 'dmf/cron/reindex' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'dmf/cron/reindex' );
		}

		update_option( 'dmf_version', DMF_VERSION );

		// CPTs are not registered during activation; flush on the next init.
		update_option( 'dmf_flush_rewrites', 1 );

		/**
		 * Fires after the framework has been activated.
		 */
		do_action( 'dmf/activated' );
	}

	/**
	 * Plugin deactivation.
	 */
	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'dmf/cron/reindex' );
		flush_rewrite_rules();

		/**
		 * Fires after the framework has been deactivated.
		 */
		do_action( 'dmf/deactivated' );
	}
}
