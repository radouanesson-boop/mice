<?php
/**
 * Zero-dependency PSR-4 autoloader used when Composer's is absent.
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
	 * Registered prefix => base directory map.
	 *
	 * @var array<string, string>
	 */
	private static array $prefixes = array();

	/**
	 * Register a namespace prefix.
	 *
	 * @param string $prefix   Namespace prefix, e.g. "DMF\\".
	 * @param string $base_dir Base directory for the prefix.
	 */
	public static function register( string $prefix, string $base_dir ): void {
		if ( empty( self::$prefixes ) ) {
			spl_autoload_register( array( self::class, 'load' ) );
		}
		self::$prefixes[ $prefix ] = rtrim( $base_dir, '/\\' ) . '/';
	}

	/**
	 * Load a class file for a fully-qualified class name.
	 *
	 * @param string $class Fully-qualified class name.
	 */
	public static function load( string $class ): void {
		foreach ( self::$prefixes as $prefix => $base_dir ) {
			if ( ! str_starts_with( $class, $prefix ) ) {
				continue;
			}

			$relative = substr( $class, strlen( $prefix ) );
			$file     = $base_dir . str_replace( '\\', '/', $relative ) . '.php';

			if ( is_readable( $file ) ) {
				require $file;
			}
			return;
		}
	}
}
