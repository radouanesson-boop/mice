<?php
/**
 * Settings sanitizer.
 *
 * @package DMF
 */

namespace DMF\Modules\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Recursive, type-preserving sanitization for the settings tree. Values are
 * cleaned by shape (bool/int/float stay typed; strings pass through
 * sanitize_text_field; emails and URLs get dedicated cleaners by key hint).
 */
class Sanitizer {

	/**
	 * Sanitize the whole settings tree.
	 *
	 * @param mixed $value Raw settings.
	 * @return array<string, mixed>
	 */
	public static function sanitize( mixed $value ): array {
		return is_array( $value ) ? self::clean_array( $value ) : array();
	}

	/**
	 * Recursively clean an array.
	 *
	 * @param array<string, mixed> $values Raw values.
	 * @return array<string, mixed>
	 */
	private static function clean_array( array $values ): array {
		$clean = array();

		foreach ( $values as $key => $item ) {
			$key = sanitize_key( (string) $key );

			$clean[ $key ] = match ( true ) {
				is_array( $item )  => self::clean_array( $item ),
				is_bool( $item )   => $item,
				is_int( $item )    => $item,
				is_float( $item )  => $item,
				default            => self::clean_scalar( $key, (string) $item ),
			};
		}

		return $clean;
	}

	/**
	 * Clean a scalar by key hint.
	 *
	 * @param string $key   Setting key.
	 * @param string $value Raw value.
	 */
	private static function clean_scalar( string $key, string $value ): string {
		if ( str_contains( $key, 'email' ) ) {
			return sanitize_email( $value );
		}
		if ( str_contains( $key, 'url' ) ) {
			return esc_url_raw( $value );
		}
		return sanitize_text_field( $value );
	}
}
