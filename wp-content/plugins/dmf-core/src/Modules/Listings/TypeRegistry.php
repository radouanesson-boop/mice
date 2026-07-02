<?php
/**
 * Listing type registry.
 *
 * @package DMF
 */

namespace DMF\Modules\Listings;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves the active listing types from three layered sources:
 *
 *   1. Framework defaults      config/listing-types.php
 *   2. Site-created types      option "dmf_listing_types" (no-code admin UI)
 *   3. Code                    `dmf/listing_types` filter (add-ons, themes)
 *
 * Later layers win on slug collision, so a site can override any default
 * without code and a developer can override anything with one filter.
 */
final class TypeRegistry {

	public const OPTION = 'dmf_listing_types';

	/** @var array<string, ListingType>|null */
	private ?array $types = null;

	/**
	 * All active types keyed by post type slug.
	 *
	 * @return array<string, ListingType>
	 */
	public function all(): array {
		if ( null !== $this->types ) {
			return $this->types;
		}

		$configs = array();

		// 1. Framework defaults.
		$defaults = require DMF_DIR . 'config/listing-types.php';
		foreach ( $defaults as $config ) {
			$configs[ $config['slug'] ] = $config;
		}

		// 2. Site-created / site-edited types.
		foreach ( (array) get_option( self::OPTION, array() ) as $config ) {
			if ( ! empty( $config['slug'] ) ) {
				$configs[ $config['slug'] ] = array_merge( $configs[ $config['slug'] ] ?? array(), $config );
			}
		}

		/**
		 * 3. Filter the raw listing type configs.
		 *
		 * @param array<string, array> $configs slug => config array.
		 */
		$configs = apply_filters( 'dmf/listing_types', $configs );

		$this->types = array();

		foreach ( $configs as $config ) {
			$type = ListingType::from_array( (array) $config );

			if ( '' !== $type->slug ) {
				$this->types[ $type->slug ] = $type;
			}
		}

		return $this->types;
	}

	public function get( string $slug ): ?ListingType {
		return $this->all()[ $slug ] ?? null;
	}

	/**
	 * Post type slugs of every listing type.
	 *
	 * @return string[]
	 */
	public function post_types(): array {
		return array_keys( $this->all() );
	}

	/**
	 * Persist a site-created type (admin UI / REST / CLI).
	 */
	public function save( ListingType $type ): void {
		$stored                 = (array) get_option( self::OPTION, array() );
		$stored[ $type->slug ]  = $type->to_array();
		update_option( self::OPTION, $stored );
		$this->types = null; // Bust cache.
	}

	/**
	 * Remove a site-created type. Framework defaults cannot be deleted,
	 * only overridden (has_archive => false hides them effectively).
	 */
	public function delete( string $slug ): void {
		$stored = (array) get_option( self::OPTION, array() );
		unset( $stored[ sanitize_key( $slug ) ] );
		update_option( self::OPTION, $stored );
		$this->types = null;
	}
}
