<?php
/**
 * Template engine.
 *
 * @package DMF
 */

namespace DMF\Modules\Templates;

use DMF\Container;
use DMF\Modules\Listings\PostTypes;
use DMF\Support\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Three-level template resolution, WooCommerce-style:
 *
 *   1. {child-theme}/dmf/{template}
 *   2. {parent-theme}/dmf/{template}
 *   3. dmf-core/templates/{template}
 *
 * The plugin always renders something sensible; themes override only what
 * they restyle. Template parts receive an $args array (WP 5.5+ semantics).
 */
class TemplatesModule extends AbstractModule {

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'templates';
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		add_filter( 'template_include', array( $this, 'route_templates' ) );

		// Expose template helper functions (dmf_part(), dmf_icon()…).
		require_once DMF_DIR . 'src/Modules/Templates/functions.php';
	}

	/**
	 * Resolve a template file through the override chain.
	 *
	 * @param string $template Relative template path, e.g. "single-listing.php".
	 * @return string Absolute path, or '' when nothing matches.
	 */
	public static function locate( string $template ): string {
		$template = ltrim( $template, '/' );

		$located = locate_template( array( 'dmf/' . $template ) );

		if ( ! $located && file_exists( DMF_DIR . 'templates/' . $template ) ) {
			$located = DMF_DIR . 'templates/' . $template;
		}

		/**
		 * Filter the located template path.
		 *
		 * @param string $located  Absolute path ('' if not found).
		 * @param string $template Requested relative template.
		 */
		return (string) apply_filters( 'dmf/templates/locate', $located, $template );
	}

	/**
	 * Render a template part with args.
	 *
	 * @param string               $template Relative template path.
	 * @param array<string, mixed> $args     Variables exposed as $args.
	 */
	public static function render( string $template, array $args = array() ): void {
		$located = self::locate( $template );

		if ( ! $located ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				printf( '<!-- dmf: missing template %s -->', esc_html( $template ) );
			}
			return;
		}

		load_template( $located, false, $args );
	}

	/**
	 * Route framework content to plugin templates when the theme doesn't
	 * provide its own single-dmf_listing.php / archive templates.
	 *
	 * @param string $template Template WordPress resolved.
	 */
	public function route_templates( string $template ): string {
		$candidates = array();

		if ( is_singular( PostTypes::LISTING ) ) {
			$candidates[] = 'single-listing.php';
		} elseif ( is_post_type_archive( PostTypes::LISTING ) || is_tax( PostTypes::TYPE_TAXONOMY ) || is_tax( PostTypes::CATEGORY ) || is_tax( PostTypes::TAG ) ) {
			$candidates[] = 'archive-listing.php';
		} elseif ( is_singular( \DMF\Modules\Events\EventsModule::POST_TYPE ) ) {
			$candidates[] = 'single-event.php';
		} elseif ( is_post_type_archive( \DMF\Modules\Events\EventsModule::POST_TYPE ) || is_tax( \DMF\Modules\Events\EventsModule::TAXONOMY ) ) {
			$candidates[] = 'archive-event.php';
		}

		if ( ! $candidates ) {
			return $template;
		}

		// Themes win: if the resolved template is already specific (not the
		// generic fallbacks), keep it.
		$basename = basename( $template );
		if ( ! in_array( $basename, array( 'index.php', 'archive.php', 'single.php', 'singular.php', 'taxonomy.php' ), true ) ) {
			return $template;
		}

		$located = self::locate( $candidates[0] );

		return $located ?: $template;
	}
}
