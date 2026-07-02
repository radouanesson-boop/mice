<?php
/**
 * Maps module.
 *
 * @package DMF
 */

namespace DMF\Modules\Maps;

use DMF\Container;
use DMF\Support\AbstractModule;
use DMF\Support\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Provider-agnostic mapping. The framework never talks to a map SDK in PHP;
 * it publishes a normalized config (`window.dmfConfig.map`) and GeoJSON-ish
 * marker payloads, and the frontend adapter (OSM/Leaflet by default, Google
 * or Mapbox by setting) renders them. Swapping providers is a setting, not
 * a rewrite.
 */
class MapsModule extends AbstractModule {

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'maps';
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		$options = $container->get( Options::class );

		add_filter(
			'dmf/frontend_config',
			static function ( array $config ) use ( $options ): array {
				$config['map'] = array(
					'provider' => (string) $options->get( 'maps.provider', 'osm' ),
					'apiKey'   => (string) $options->get( 'maps.api_key', '' ),
					'center'   => array(
						'lat' => (float) $options->get( 'maps.default_lat', 31.6295 ),
						'lng' => (float) $options->get( 'maps.default_lng', -7.9811 ),
					),
					'zoom'     => (int) $options->get( 'maps.default_zoom', 12 ),
					'cluster'  => (bool) $options->get( 'maps.cluster', true ),
				);
				return $config;
			}
		);
	}

	/**
	 * Marker payload for a set of listings, consumed by the JS map adapter.
	 *
	 * @param int[] $listing_ids Listing IDs.
	 * @return array<int, array<string, mixed>>
	 */
	public static function markers( array $listing_ids ): array {
		$markers = array();

		foreach ( $listing_ids as $id ) {
			$geo = (array) \DMF\Modules\Fields\FieldRegistry::value( (int) $id, 'geo', array() );

			if ( ! isset( $geo['lat'], $geo['lng'] ) ) {
				continue;
			}

			$markers[] = array(
				'id'    => (int) $id,
				'lat'   => (float) $geo['lat'],
				'lng'   => (float) $geo['lng'],
				'title' => get_the_title( (int) $id ),
				'url'   => (string) get_permalink( (int) $id ),
				'type'  => \DMF\Modules\Listings\PostTypes::listing_type_slug( (int) $id ),
				'image' => (string) get_the_post_thumbnail_url( (int) $id, 'medium' ),
			);
		}

		/**
		 * Filter map marker payloads.
		 *
		 * @param array<int, array<string, mixed>> $markers     Marker payloads.
		 * @param int[]                            $listing_ids Source listing IDs.
		 */
		return (array) apply_filters( 'dmf/maps/markers', $markers, $listing_ids );
	}
}
