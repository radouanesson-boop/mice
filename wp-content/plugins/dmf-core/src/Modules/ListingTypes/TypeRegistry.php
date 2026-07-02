<?php
/**
 * Listing type registry.
 *
 * @package DMF
 */

namespace DMF\Modules\ListingTypes;

use DMF\Modules\Listings\PostTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Source of truth for listing types. Types are stored in one option and
 * mirrored to `dmf_listing_type` taxonomy terms so archives, rewrite rules
 * and term counts come from core WordPress for free.
 */
class TypeRegistry {

	public const OPTION = 'dmf_listing_types';

	/**
	 * Runtime cache.
	 *
	 * @var array<string, ListingType>|null
	 */
	private ?array $types = null;

	/**
	 * All registered types keyed by slug.
	 *
	 * @return array<string, ListingType>
	 */
	public function all(): array {
		if ( null === $this->types ) {
			$stored = (array) get_option( self::OPTION, array() );

			/**
			 * Filter listing type configs before hydration. Extensions can
			 * inject code-defined types (marked builtin) here.
			 *
			 * @param array<int|string, array> $stored Raw type configs.
			 */
			$stored = (array) apply_filters( 'dmf/listing_types', $stored );

			$this->types = array();
			foreach ( $stored as $config ) {
				$type = ListingType::from_array( (array) $config );
				if ( '' !== $type->slug ) {
					$this->types[ $type->slug ] = $type;
				}
			}
		}

		return $this->types;
	}

	/**
	 * A single type.
	 *
	 * @param string $slug Type slug.
	 */
	public function get( string $slug ): ?ListingType {
		return $this->all()[ $slug ] ?? null;
	}

	/**
	 * Create or update a type and sync its taxonomy term.
	 *
	 * @param ListingType $type Type to persist.
	 */
	public function save( ListingType $type ): ListingType {
		$stored                = $this->raw();
		$stored[ $type->slug ] = $type->to_array();

		update_option( self::OPTION, $stored );
		$this->types = null;

		$this->sync_term( $type );

		/**
		 * Fires when a listing type is created or updated.
		 *
		 * @param ListingType $type The saved type.
		 */
		do_action( 'dmf/listing_type/saved', $type );

		return $type;
	}

	/**
	 * Delete a type (its term stays so content is never orphaned silently).
	 *
	 * @param string $slug Type slug.
	 */
	public function delete( string $slug ): bool {
		$stored = $this->raw();

		if ( ! isset( $stored[ $slug ] ) ) {
			return false;
		}

		unset( $stored[ $slug ] );
		update_option( self::OPTION, $stored );
		$this->types = null;

		/**
		 * Fires when a listing type is deleted.
		 *
		 * @param string $slug The deleted type slug.
		 */
		do_action( 'dmf/listing_type/deleted', $slug );

		return true;
	}

	/**
	 * Ensure every stored type has a matching taxonomy term.
	 */
	public function sync_terms(): void {
		foreach ( $this->all() as $type ) {
			$this->sync_term( $type );
		}
	}

	/**
	 * The stored option, keyed by slug.
	 *
	 * @return array<string, array>
	 */
	private function raw(): array {
		$stored = (array) get_option( self::OPTION, array() );
		$keyed  = array();
		foreach ( $stored as $config ) {
			if ( ! empty( $config['slug'] ) ) {
				$keyed[ (string) $config['slug'] ] = (array) $config;
			}
		}
		return $keyed;
	}

	/**
	 * Mirror one type to a `dmf_listing_type` term.
	 *
	 * @param ListingType $type Type to mirror.
	 */
	private function sync_term( ListingType $type ): void {
		if ( ! taxonomy_exists( PostTypes::TYPE_TAXONOMY ) ) {
			return;
		}

		$term = get_term_by( 'slug', $type->slug, PostTypes::TYPE_TAXONOMY );

		if ( $term instanceof \WP_Term ) {
			wp_update_term(
				$term->term_id,
				PostTypes::TYPE_TAXONOMY,
				array(
					'name'        => $type->name,
					'description' => $type->description,
				)
			);
		} else {
			wp_insert_term(
				$type->name,
				PostTypes::TYPE_TAXONOMY,
				array(
					'slug'        => $type->slug,
					'description' => $type->description,
				)
			);
		}
	}
}
