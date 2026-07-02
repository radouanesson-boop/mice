<?php
/**
 * WP-CLI module.
 *
 * @package DMF
 */

namespace DMF\Modules\Cli;

use DMF\Modules\AbstractModule;
use DMF\Modules\Listings\ListingType;

defined( 'ABSPATH' ) || exit;

/**
 * `wp dmf <command>` — operational tooling.
 *
 *   wp dmf types                 List active listing types.
 *   wp dmf seed --count=5        Create demo listings per type.
 *   wp dmf export-types          Print listing type configs as JSON.
 */
final class CliModule extends AbstractModule {

	public function register(): void {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		\WP_CLI::add_command( 'dmf types', array( $this, 'types' ) );
		\WP_CLI::add_command( 'dmf seed', array( $this, 'seed' ) );
		\WP_CLI::add_command( 'dmf export-types', array( $this, 'export_types' ) );
	}

	/**
	 * List active listing types.
	 */
	public function types(): void {
		$rows = array_map(
			static fn( ListingType $type ) => array(
				'slug'    => $type->slug,
				'plural'  => $type->plural,
				'kind'    => $type->kind,
				'rewrite' => $type->rewrite,
			),
			array_values( dmf()->module( 'listings' )->registry()->all() )
		);

		\WP_CLI\Utils\format_items( 'table', $rows, array( 'slug', 'plural', 'kind', 'rewrite' ) );
	}

	/**
	 * Seed demo listings.
	 *
	 * ## OPTIONS
	 *
	 * [--count=<n>]
	 * : Listings per type. Default 3.
	 */
	public function seed( array $args, array $assoc ): void {
		$count = max( 1, (int) ( $assoc['count'] ?? 3 ) );

		foreach ( dmf()->module( 'listings' )->registry()->all() as $type ) {
			for ( $i = 1; $i <= $count; $i++ ) {
				wp_insert_post(
					array(
						'post_type'    => $type->slug,
						'post_status'  => 'publish',
						'post_title'   => sprintf( '%s %d (demo)', $type->singular, $i ),
						'post_excerpt' => sprintf( 'Demo %s seeded by wp dmf seed.', strtolower( $type->singular ) ),
					)
				);
			}

			\WP_CLI::log( sprintf( 'Seeded %d × %s', $count, $type->plural ) );
		}

		\WP_CLI::success( 'Done.' );
	}

	/**
	 * Print listing type configs as JSON (for versioning/migration).
	 */
	public function export_types(): void {
		$configs = array_map(
			static fn( ListingType $type ) => $type->to_array(),
			array_values( dmf()->module( 'listings' )->registry()->all() )
		);

		\WP_CLI::print_value( $configs, array( 'format' => 'json' ) );
	}
}
