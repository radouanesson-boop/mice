<?php
/**
 * I18n module — translations & RTL.
 *
 * @package DMF
 */

namespace DMF\Modules\I18n;

use DMF\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Every DMF string uses the `dmf` textdomain. Multilingual content is
 * delegated to Polylang/WPML (both register listing CPTs automatically
 * via show_in_rest + public); this module makes DMF a first-class citizen
 * for them and keeps RTL working out of the box.
 */
final class I18nModule extends AbstractModule {

	public function register(): void {
		add_action( 'init', static function (): void {
			load_plugin_textdomain( 'dmf', false, dirname( plugin_basename( DMF_FILE ) ) . '/languages' );
		} );

		// Declare listing post types translatable in Polylang.
		add_filter( 'pll_get_post_types', array( $this, 'polylang_post_types' ), 10, 2 );

		// RTL flag for front-end assets/themes.
		add_filter( 'body_class', static function ( array $classes ): array {
			if ( is_rtl() ) {
				$classes[] = 'dmf-rtl';
			}
			return $classes;
		} );
	}

	public function polylang_post_types( array $post_types, bool $is_settings ): array {
		foreach ( dmf()->module( 'listings' )->registry()->post_types() as $slug ) {
			$post_types[ $slug ] = $slug;
		}

		return $post_types;
	}
}
