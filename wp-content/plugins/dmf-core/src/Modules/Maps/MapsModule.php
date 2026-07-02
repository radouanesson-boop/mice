<?php
/**
 * Maps module — provider-agnostic geo layer.
 *
 * @package DMF
 */

namespace DMF\Modules\Maps;

use DMF\Modules\AbstractModule;
use DMF\Support\Options;

defined( 'ABSPATH' ) || exit;

/**
 * DMF never binds to one map vendor. Listings store lat/lng in the `geo`
 * field; this module exposes a uniform JS config (provider, keys, center)
 * and GeoJSON via REST. The front-end adapter (theme/add-on) reads
 * `window.dmfMaps` and initializes Google Maps, Mapbox or Leaflet/OSM —
 * clustering and draw-search live in the adapter, data lives here.
 */
final class MapsModule extends AbstractModule {

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'routes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'config' ), 5 );
	}

	/**
	 * Expose map configuration for front-end adapters.
	 */
	public function config(): void {
		$config = array(
			'provider' => Options::get( 'maps.provider', 'osm' ), // osm|google|mapbox
			'apiKey'   => Options::get( 'maps.api_key', '' ),
			'center'   => array(
				'lat' => (float) Options::get( 'maps.center_lat', 31.6295 ), // Marrakech.
				'lng' => (float) Options::get( 'maps.center_lng', -7.9811 ),
			),
			'zoom'     => (int) Options::get( 'maps.zoom', 12 ),
			'geojson'  => rest_url( 'dmf/v1/geojson' ),
		);

		wp_register_script( 'dmf-maps-config', '', array(), DMF_VERSION, true );
		wp_enqueue_script( 'dmf-maps-config' );
		wp_add_inline_script( 'dmf-maps-config', 'window.dmfMaps = ' . wp_json_encode( $config ) . ';' );
	}

	public function routes(): void {
		register_rest_route(
			'dmf/v1',
			'/geojson',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'geojson' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'type' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
				),
			)
		);
	}

	/**
	 * All published, geolocated listings as a GeoJSON FeatureCollection —
	 * consumable directly by every major map library.
	 */
	public function geojson( \WP_REST_Request $request ): \WP_REST_Response {
		$post_types = dmf()->module( 'listings' )->registry()->post_types();

		if ( $request['type'] ) {
			$wanted     = array_map( 'sanitize_key', explode( ',', (string) $request['type'] ) );
			$post_types = array_values( array_intersect( $post_types, $wanted ) ) ?: $post_types;
		}

		$query = new \WP_Query(
			array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => 500,
				'no_found_rows'  => true,
				'meta_key'       => '_dmf_geo', // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);

		$features = array();

		foreach ( $query->posts as $post ) {
			$geo = get_post_meta( $post->ID, '_dmf_geo', true );

			if ( ! is_array( $geo ) || '' === ( $geo['lat'] ?? '' ) ) {
				continue;
			}

			$features[] = array(
				'type'       => 'Feature',
				'geometry'   => array(
					'type'        => 'Point',
					'coordinates' => array( (float) $geo['lng'], (float) $geo['lat'] ),
				),
				'properties' => array(
					'id'    => $post->ID,
					'title' => get_the_title( $post ),
					'url'   => get_permalink( $post ),
					'type'  => $post->post_type,
					'image' => get_the_post_thumbnail_url( $post, 'thumbnail' ) ?: null,
				),
			);
		}

		return rest_ensure_response(
			array(
				'type'     => 'FeatureCollection',
				'features' => $features,
			)
		);
	}
}
