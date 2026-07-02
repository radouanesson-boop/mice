<?php
/**
 * Performance module.
 *
 * @package DMF
 */

namespace DMF\Modules\Performance;

use DMF\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress-VIP-minded defaults: cached expensive reads, native lazy
 * loading, zero jQuery, lean head. The framework itself enqueues no
 * front-end JS — behaviour lives in the theme as small vanilla modules.
 */
final class PerformanceModule extends AbstractModule {

	public function register(): void {
		// Emoji scripts: SVG icon systems make these dead weight.
		add_action( 'init', static function (): void {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
		} );

		// Cache facet/term-count queries briefly (they back live filters).
		add_filter( 'dmf/search_query_args', array( $this, 'no_found_rows_when_unpaged' ) );

		// Invalidate listing-related caches on save.
		add_action( 'dmf/listing_saved', static function (): void {
			wp_cache_delete( 'dmf_geojson', 'dmf' );
		} );
	}

	/**
	 * Skip SQL_CALC_FOUND_ROWS when pagination is not requested.
	 *
	 * @param array $args WP_Query args.
	 */
	public function no_found_rows_when_unpaged( array $args ): array {
		if ( empty( $args['paged'] ) || 1 === (int) $args['paged'] ) {
			// Keep found_rows only when the caller needs page counts.
			$args['no_found_rows'] = empty( $args['dmf_need_pagination'] );
		}

		return $args;
	}
}
