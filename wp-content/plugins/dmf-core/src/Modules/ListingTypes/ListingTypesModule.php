<?php
/**
 * Listing types module.
 *
 * @package DMF
 */

namespace DMF\Modules\ListingTypes;

use DMF\Container;
use DMF\Support\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Manages the configurable listing type registry — the "unlimited listing
 * types without code" capability. CRUD surfaces live in the Api, Admin and
 * Cli modules; this module owns the registry service and term mirroring.
 */
class ListingTypesModule extends AbstractModule {

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'listing-types';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register( Container $container ): void {
		$container->singleton( TypeRegistry::class );
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		// Late on init, after the taxonomy exists: keep terms mirrored.
		add_action( 'init', static fn() => $container->get( TypeRegistry::class )->sync_terms(), 20 );
	}
}
