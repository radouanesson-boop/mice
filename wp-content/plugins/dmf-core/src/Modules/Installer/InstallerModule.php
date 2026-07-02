<?php
/**
 * Installer module.
 *
 * @package DMF
 */

namespace DMF\Modules\Installer;

use DMF\Container;
use DMF\Support\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Runtime side of the lifecycle: version-change upgrade routines and the
 * deferred rewrite flush requested by the activator.
 */
class InstallerModule extends AbstractModule {

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'installer';
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		add_action( 'init', array( $this, 'maybe_upgrade' ), 1 );
		add_action( 'init', array( $this, 'maybe_flush_rewrites' ), 99 );
	}

	/**
	 * Run upgrade routines when the stored version lags the code version.
	 */
	public function maybe_upgrade(): void {
		$installed = (string) get_option( 'dmf_version', '' );

		if ( DMF_VERSION === $installed ) {
			return;
		}

		Schema::migrate();
		Roles::install();

		update_option( 'dmf_version', DMF_VERSION );
		update_option( 'dmf_flush_rewrites', 1 );

		/**
		 * Fires after a version upgrade routine.
		 *
		 * @param string $from Previous version ('' on fresh installs).
		 * @param string $to   New version.
		 */
		do_action( 'dmf/upgraded', $installed, DMF_VERSION );
	}

	/**
	 * Flush rewrite rules once after activation/upgrade, post CPT registration.
	 */
	public function maybe_flush_rewrites(): void {
		if ( get_option( 'dmf_flush_rewrites' ) ) {
			delete_option( 'dmf_flush_rewrites' );
			flush_rewrite_rules();
		}
	}
}
