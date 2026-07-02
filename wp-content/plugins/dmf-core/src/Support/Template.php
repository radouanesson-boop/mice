<?php
/**
 * Template engine.
 *
 * @package DMF
 */

namespace DMF\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Plain-PHP template locator with a theme override chain — the same
 * update-safe pattern WooCommerce made standard:
 *
 *   1. {child-theme}/dmf/{template}.php
 *   2. {parent-theme}/dmf/{template}.php
 *   3. {plugin}/templates/{template}.php
 *
 * Templates receive data as an extracted `$args` array plus individual
 * variables, and every path is filterable.
 */
final class Template {

	/**
	 * Locate a template file.
	 *
	 * @param string $name Relative name without extension, e.g. "cards/listing".
	 */
	public static function locate( string $name ): string {
		$name = ltrim( str_replace( array( '..', "\0" ), '', $name ), '/' );

		$theme_file = locate_template( array( "dmf/{$name}.php" ) );
		$file       = $theme_file ?: DMF_DIR . "templates/{$name}.php";

		/**
		 * Filter the resolved template path.
		 *
		 * @param string $file Absolute path.
		 * @param string $name Template name.
		 */
		return apply_filters( 'dmf/template', $file, $name );
	}

	/**
	 * Render a template.
	 *
	 * @param string               $name Template name, e.g. "cards/listing".
	 * @param array<string, mixed> $args Data available as $args in the template.
	 * @param bool                 $echo Echo (default) or return.
	 */
	public static function render( string $name, array $args = array(), bool $echo = true ): string {
		$file = self::locate( $name );

		if ( ! is_readable( $file ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( "[DMF] Missing template: {$name}" ); // phpcs:ignore
			}
			return '';
		}

		if ( ! $echo ) {
			ob_start();
		}

		( static function ( string $__dmf_file, array $args ): void {
			require $__dmf_file;
		} )( $file, $args );

		return $echo ? '' : (string) ob_get_clean();
	}
}
