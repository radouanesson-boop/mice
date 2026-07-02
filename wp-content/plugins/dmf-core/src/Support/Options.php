<?php
/**
 * Settings access.
 *
 * @package DMF
 */

namespace DMF\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Single-option settings store (one autoloaded array, namespaced keys) with
 * filterable reads so config can be forced from code per environment:
 *
 *     add_filter( 'dmf/option/maps.provider', fn() => 'osm' );
 */
final class Options {

	public const OPTION = 'dmf_settings';

	/**
	 * Read a setting.
	 *
	 * @param string $key     Dot key, e.g. "maps.provider".
	 * @param mixed  $default Fallback.
	 */
	public static function get( string $key, mixed $default = null ): mixed {
		$all   = (array) get_option( self::OPTION, array() );
		$value = $all[ $key ] ?? $default;

		/** Documented in class docblock. */
		return apply_filters( "dmf/option/{$key}", $value );
	}

	/**
	 * Write a setting.
	 */
	public static function set( string $key, mixed $value ): void {
		$all         = (array) get_option( self::OPTION, array() );
		$all[ $key ] = $value;
		update_option( self::OPTION, $all );
	}

	/**
	 * All settings (unfiltered).
	 *
	 * @return array<string, mixed>
	 */
	public static function all(): array {
		return (array) get_option( self::OPTION, array() );
	}
}
