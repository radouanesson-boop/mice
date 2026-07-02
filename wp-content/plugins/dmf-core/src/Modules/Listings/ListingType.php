<?php
/**
 * Listing type definition.
 *
 * @package DMF
 */

namespace DMF\Modules\Listings;

defined( 'ABSPATH' ) || exit;

/**
 * Immutable value object describing one listing type (Convention Centre,
 * Hotel, DMC, Restaurant…). Types are pure configuration — see
 * config/listing-types.php and the no-code admin creator.
 */
final class ListingType {

	/**
	 * @param string                $slug        Post type key (≤20 chars), e.g. "dmf_venue".
	 * @param string                $singular    Singular label.
	 * @param string                $plural      Plural label.
	 * @param string                $rewrite     URL base, e.g. "venues".
	 * @param string                $icon        Dashicon or SVG data URI for wp-admin.
	 * @param string                $kind        Card/schema family: venue|hotel|restaurant|experience|event|supplier|generic.
	 * @param array<string>         $taxonomies  Shared taxonomy keys this type uses.
	 * @param array<string, array>  $fields      Field groups: group-key => [ label, fields[] ].
	 * @param bool                  $has_archive Whether the type has a public archive.
	 */
	public function __construct(
		public readonly string $slug,
		public readonly string $singular,
		public readonly string $plural,
		public readonly string $rewrite,
		public readonly string $icon = 'dashicons-location-alt',
		public readonly string $kind = 'generic',
		public readonly array $taxonomies = array( 'dmf_category', 'dmf_area', 'dmf_tag' ),
		public readonly array $fields = array(),
		public readonly bool $has_archive = true,
	) {}

	/**
	 * Build from a config array (config file, DB option, REST payload).
	 *
	 * @param array<string, mixed> $config Raw definition.
	 */
	public static function from_array( array $config ): self {
		$slug = substr( sanitize_key( $config['slug'] ?? '' ), 0, 20 );

		return new self(
			$slug,
			(string) ( $config['singular'] ?? ucfirst( $slug ) ),
			(string) ( $config['plural'] ?? ucfirst( $slug ) . 's' ),
			sanitize_title( $config['rewrite'] ?? $config['plural'] ?? $slug ),
			(string) ( $config['icon'] ?? 'dashicons-location-alt' ),
			sanitize_key( $config['kind'] ?? 'generic' ),
			array_map( 'sanitize_key', (array) ( $config['taxonomies'] ?? array( 'dmf_category', 'dmf_area', 'dmf_tag' ) ) ),
			(array) ( $config['fields'] ?? array() ),
			(bool) ( $config['has_archive'] ?? true ),
		);
	}

	/**
	 * Export as config array (for the admin editor and REST).
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return array(
			'slug'        => $this->slug,
			'singular'    => $this->singular,
			'plural'      => $this->plural,
			'rewrite'     => $this->rewrite,
			'icon'        => $this->icon,
			'kind'        => $this->kind,
			'taxonomies'  => $this->taxonomies,
			'fields'      => $this->fields,
			'has_archive' => $this->has_archive,
		);
	}
}
