<?php
/**
 * Field group registry.
 *
 * @package DMF
 */

namespace DMF\Modules\Fields;

use DMF\Modules\ListingTypes\TypeRegistry;

defined( 'ABSPATH' ) || exit;

/**
 * Field groups are reusable bundles of field definitions attached to listing
 * types by key ("capacity" is shared by convention centers, hotels and
 * activity venues). Stored as configuration; extendable by filter.
 */
class FieldRegistry {

	public const OPTION      = 'dmf_field_groups';
	public const META_PREFIX = '_dmf_';

	/**
	 * Constructor.
	 *
	 * @param TypeRegistry $types Listing type registry.
	 */
	public function __construct( private TypeRegistry $types ) {}

	/**
	 * Runtime cache.
	 *
	 * @var array<string, array>|null
	 */
	private ?array $groups = null;

	/**
	 * All field groups keyed by group key.
	 *
	 * @return array<string, array>
	 */
	public function groups(): array {
		if ( null === $this->groups ) {
			$stored = (array) get_option( self::OPTION, array() );

			/**
			 * Filter field group definitions.
			 *
			 * @param array<string, array> $stored Groups keyed by group key.
			 */
			$this->groups = (array) apply_filters( 'dmf/fields/groups', $stored );
		}

		return $this->groups;
	}

	/**
	 * One group.
	 *
	 * @param string $key Group key.
	 * @return array<string, mixed>|null
	 */
	public function group( string $key ): ?array {
		return $this->groups()[ $key ] ?? null;
	}

	/**
	 * Persist a group definition.
	 *
	 * @param string               $key   Group key.
	 * @param array<string, mixed> $group Group definition.
	 */
	public function save_group( string $key, array $group ): void {
		$stored         = (array) get_option( self::OPTION, array() );
		$group['key']   = sanitize_key( $key );
		$stored[ $key ] = $group;

		update_option( self::OPTION, $stored );
		$this->groups = null;
	}

	/**
	 * Delete a group definition.
	 *
	 * @param string $key Group key.
	 */
	public function delete_group( string $key ): bool {
		$stored = (array) get_option( self::OPTION, array() );
		if ( ! isset( $stored[ $key ] ) ) {
			return false;
		}
		unset( $stored[ $key ] );
		update_option( self::OPTION, $stored );
		$this->groups = null;
		return true;
	}

	/**
	 * Groups attached to a listing type, in the type's configured order.
	 *
	 * @param string $type_slug Listing type slug.
	 * @return array<string, array>
	 */
	public function groups_for_type( string $type_slug ): array {
		$type = $this->types->get( $type_slug );
		if ( ! $type ) {
			return array();
		}

		$groups = array();
		foreach ( $type->field_groups as $key ) {
			$group = $this->group( $key );
			if ( $group ) {
				$groups[ $key ] = $group;
			}
		}

		return $groups;
	}

	/**
	 * Flat field definitions for a listing type, keyed by field key.
	 *
	 * @param string $type_slug Listing type slug.
	 * @return array<string, array>
	 */
	public function fields_for_type( string $type_slug ): array {
		$fields = array();
		foreach ( $this->groups_for_type( $type_slug ) as $group ) {
			foreach ( (array) ( $group['fields'] ?? array() ) as $field ) {
				if ( ! empty( $field['key'] ) ) {
					$fields[ (string) $field['key'] ] = (array) $field;
				}
			}
		}
		return $fields;
	}

	/**
	 * Every field key known to the framework (all groups) — used by the
	 * importer and the search indexer.
	 *
	 * @return array<string, array>
	 */
	public function all_fields(): array {
		$fields = array();
		foreach ( $this->groups() as $group ) {
			foreach ( (array) ( $group['fields'] ?? array() ) as $field ) {
				if ( ! empty( $field['key'] ) ) {
					$fields[ (string) $field['key'] ] = (array) $field;
				}
			}
		}
		return $fields;
	}

	/**
	 * Read a field value for a post.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Field key (unprefixed).
	 * @param mixed  $fallback Fallback value.
	 */
	public static function value( int $post_id, string $key, mixed $fallback = '' ): mixed {
		$value = get_post_meta( $post_id, self::META_PREFIX . $key, true );
		return ( '' === $value || null === $value ) ? $fallback : $value;
	}

	/**
	 * Write a field value.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Field key (unprefixed).
	 * @param mixed  $value   Sanitized value.
	 */
	public static function update( int $post_id, string $key, mixed $value ): void {
		if ( '' === $value || array() === $value || null === $value ) {
			delete_post_meta( $post_id, self::META_PREFIX . $key );
		} else {
			update_post_meta( $post_id, self::META_PREFIX . $key, $value );
		}
	}
}
