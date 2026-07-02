<?php
/**
 * Bundled PSR-4 autoloader — used when Composer's autoloader is absent so
 * the plugin runs from a plain zip install with zero build steps.
 *
 * @package DMF
 */

namespace DMF;

defined( 'ABSPATH' ) || exit;

/**
 * Minimal PSR-4 autoloader.
 */
final class Autoloader {

	/**
	 * Register a namespace prefix → base directory mapping.
	 *
	 * @param string $prefix   Namespace prefix, e.g. "DMF\\".
	 * @param string $base_dir Absolute base directory for the prefix.
	 */
	public static function register( string $prefix, string $base_dir ): void {
		spl_autoload_register(
			static function ( string $class ) use ( $prefix, $base_dir ): void {
				if ( ! str_starts_with( $class, $prefix ) ) {
					return;
				}

				$relative = substr( $class, strlen( $prefix ) );
				$file     = $base_dir . str_replace( '\\', '/', $relative ) . '.php';

				if ( is_readable( $file ) ) {
					require $file;
				}
			}
		);
	}
}
