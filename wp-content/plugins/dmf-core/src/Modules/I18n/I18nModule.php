<?php
/**
 * Localization module.
 *
 * @package DMF
 */

namespace DMF\Modules\I18n;

use DMF\Container;
use DMF\Support\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Loads translations and exposes RTL/locale helpers. The framework ships
 * every user-facing string through the `dmf` text domain; per-destination
 * language packs live in wp-content/languages/plugins.
 */
class I18nModule extends AbstractModule {

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'i18n';
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_filter( 'body_class', array( $this, 'rtl_body_class' ) );
	}

	/**
	 * Load the plugin text domain.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'dmf', false, dirname( plugin_basename( DMF_FILE ) ) . '/languages' );
	}

	/**
	 * Add an `dmf-rtl` body class so the design system can flip layout
	 * primitives (Arabic is a first-class locale for Moroccan destinations).
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function rtl_body_class( array $classes ): array {
		if ( is_rtl() ) {
			$classes[] = 'dmf-rtl';
		}
		return $classes;
	}

	/**
	 * Locales the destination publishes in, filterable per site.
	 *
	 * @return string[]
	 */
	public static function locales(): array {
		/**
		 * Filter the destination's published locales.
		 *
		 * @param string[] $locales WP locale codes.
		 */
		return (array) apply_filters(
			'dmf/i18n/locales',
			array( 'fr_FR', 'en_US', 'ar', 'es_ES', 'de_DE', 'it_IT', 'pt_PT' )
		);
	}
}
