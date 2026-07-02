<?php
/**
 * Framework settings accessor.
 *
 * @package DMF
 */

namespace DMF\Support;

defined( 'ABSPATH' ) || exit;

/**
 * All framework settings live in one autoloaded option (`dmf_settings`) with
 * dot-notation access and filterable defaults, so a child theme or extension
 * can pre-configure a whole destination in a single `dmf/settings/defaults`
 * filter (the "new city = new config" promise).
 */
class Options {

	public const OPTION = 'dmf_settings';

	/**
	 * Cached merged settings.
	 *
	 * @var array<string, mixed>|null
	 */
	private ?array $cache = null;

	/**
	 * Framework defaults.
	 *
	 * @return array<string, mixed>
	 */
	public function defaults(): array {
		$defaults = array(
			'general'       => array(
				'destination_name' => get_bloginfo( 'name' ),
				'default_country'  => 'MA',
				'default_city'     => 'Marrakech',
				'units'            => 'metric',
			),
			'listings'      => array(
				'archive_slug'      => 'listings',
				'per_page'          => 12,
				'default_sort'      => 'featured',
				'enable_favorites'  => true,
				'enable_reviews'    => true,
				'moderate_listings' => true,
			),
			'search'        => array(
				'live_search'     => true,
				'autocomplete'    => true,
				'radius_default'  => 25,
				'radius_max'      => 100,
				'log_searches'    => true,
			),
			'maps'          => array(
				'provider'       => 'osm', // osm | google | mapbox.
				'api_key'        => '',
				'default_lat'    => 31.6295,
				'default_lng'    => -7.9811,
				'default_zoom'   => 12,
				'cluster'        => true,
			),
			'seo'           => array(
				'schema'      => true,
				'opengraph'   => true,
				'breadcrumbs' => true,
			),
			'performance'   => array(
				'defer_js'     => true,
				'preload_css'  => true,
				'lazy_images'  => true,
			),
			'notifications' => array(
				'from_name'     => get_bloginfo( 'name' ),
				'from_email'    => get_bloginfo( 'admin_email' ),
				'inquiry_admin' => true,
				'inquiry_owner' => true,
			),
		);

		/**
		 * Filter framework default settings. Destination child themes use this
		 * to pre-configure a city without touching the database.
		 *
		 * @param array<string, mixed> $defaults Default settings tree.
		 */
		return (array) apply_filters( 'dmf/settings/defaults', $defaults );
	}

	/**
	 * Full merged settings tree.
	 *
	 * @return array<string, mixed>
	 */
	public function all(): array {
		if ( null === $this->cache ) {
			$saved       = (array) get_option( self::OPTION, array() );
			$this->cache = array_replace_recursive( $this->defaults(), $saved );
		}
		return $this->cache;
	}

	/**
	 * Read a setting via dot notation, e.g. get( 'maps.provider' ).
	 *
	 * @param string $key      Dot-notation key.
	 * @param mixed  $fallback Value when the key is missing.
	 */
	public function get( string $key, mixed $fallback = null ): mixed {
		$value = $this->all();

		foreach ( explode( '.', $key ) as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return $fallback;
			}
			$value = $value[ $segment ];
		}

		return $value;
	}

	/**
	 * Write a setting via dot notation and persist.
	 *
	 * @param string $key   Dot-notation key.
	 * @param mixed  $value New value.
	 */
	public function set( string $key, mixed $value ): void {
		$saved = (array) get_option( self::OPTION, array() );
		$node  =& $saved;

		$segments = explode( '.', $key );
		$last     = array_pop( $segments );

		foreach ( $segments as $segment ) {
			if ( ! isset( $node[ $segment ] ) || ! is_array( $node[ $segment ] ) ) {
				$node[ $segment ] = array();
			}
			$node =& $node[ $segment ];
		}

		$node[ $last ] = $value;

		update_option( self::OPTION, $saved );
		$this->cache = null;
	}

	/**
	 * Replace a whole settings section (used by the settings REST endpoint).
	 *
	 * @param string               $section Section key, e.g. "maps".
	 * @param array<string, mixed> $values  Section values.
	 */
	public function set_section( string $section, array $values ): void {
		$saved             = (array) get_option( self::OPTION, array() );
		$saved[ $section ] = $values;

		update_option( self::OPTION, $saved );
		$this->cache = null;
	}
}
