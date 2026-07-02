<?php
/**
 * API module — the dmf/v1 REST namespace surface.
 *
 * @package DMF
 */

namespace DMF\Modules\Api;

use DMF\Modules\AbstractModule;
use DMF\Modules\Listings\ListingType;
use DMF\Modules\Listings\TypeRegistry;

defined( 'ABSPATH' ) || exit;

/**
 * Cross-cutting REST endpoints. Feature endpoints live in their modules
 * (search, geojson, favorites, inquiries, events, me); this module owns
 * the framework-level ones:
 *
 *   GET  /dmf/v1/types          public — active listing types (for apps/filters)
 *   POST /dmf/v1/types          manage_options — create/update a type (no-code)
 *   GET  /dmf/v1/status         manage_options — system health snapshot
 */
final class ApiModule extends AbstractModule {

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'routes' ) );
	}

	public function routes(): void {
		register_rest_route(
			'dmf/v1',
			'/types',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_types' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_type' ),
					'permission_callback' => fn() => current_user_can( 'manage_options' ),
				),
			)
		);

		register_rest_route(
			'dmf/v1',
			'/status',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'status' ),
				'permission_callback' => fn() => current_user_can( 'manage_options' ),
			)
		);
	}

	public function get_types(): \WP_REST_Response {
		$types = array_map(
			static fn( ListingType $type ) => array(
				'slug'     => $type->slug,
				'singular' => $type->singular,
				'plural'   => $type->plural,
				'kind'     => $type->kind,
				'archive'  => $type->has_archive ? get_post_type_archive_link( $type->slug ) : null,
			),
			array_values( dmf()->module( 'listings' )->registry()->all() )
		);

		return rest_ensure_response( array( 'types' => $types ) );
	}

	public function save_type( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$config = (array) $request->get_json_params();
		$type   = ListingType::from_array( $config );

		if ( '' === $type->slug || ! str_starts_with( $type->slug, 'dmf_' ) ) {
			return new \WP_Error(
				'dmf_bad_type',
				__( 'Listing type slugs must start with "dmf_" and be at most 20 characters.', 'dmf' ),
				array( 'status' => 400 )
			);
		}

		dmf()->module( 'listings' )->registry()->save( $type );
		flush_rewrite_rules();

		return rest_ensure_response( array( 'saved' => $type->to_array() ) );
	}

	public function status(): \WP_REST_Response {
		global $wp_version;

		$registry = dmf()->module( 'listings' )->registry();
		$counts   = array();

		foreach ( $registry->post_types() as $post_type ) {
			$counts[ $post_type ] = (int) ( wp_count_posts( $post_type )->publish ?? 0 );
		}

		return rest_ensure_response(
			array(
				'dmf'       => DMF_VERSION,
				'wordpress' => $wp_version,
				'php'       => PHP_VERSION,
				'types'     => count( $registry->all() ),
				'published' => $counts,
			)
		);
	}
}
