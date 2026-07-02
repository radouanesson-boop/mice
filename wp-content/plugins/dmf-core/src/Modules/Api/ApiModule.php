<?php
/**
 * REST API module.
 *
 * @package DMF
 */

namespace DMF\Modules\Api;

use DMF\Container;
use DMF\Support\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * The `dmf/v1` namespace: everything the frontend (and headless consumers)
 * need — search, suggestions, map markers, favorites, reviews, inquiries,
 * listing types and settings. Write operations are nonce- or capability-
 * gated; public reads are cache-friendly GETs.
 */
class ApiModule extends AbstractModule {

	public const NAMESPACE = 'dmf/v1';

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'api';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register( Container $container ): void {
		$container->singleton( Routes::class );
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		add_action( 'rest_api_init', static fn() => $container->get( Routes::class )->register_routes() );

		// Frontend bootstrap config shared by theme scripts.
		add_filter(
			'dmf/frontend_config',
			static function ( array $config ): array {
				$config['restUrl'] = esc_url_raw( rest_url( self::NAMESPACE ) );
				$config['nonce']   = wp_create_nonce( 'wp_rest' );
				$config['loggedIn'] = is_user_logged_in();
				return $config;
			},
			1
		);
	}
}
