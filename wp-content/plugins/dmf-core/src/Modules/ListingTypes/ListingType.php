<?php
/**
 * Listing type value object.
 *
 * @package DMF
 */

namespace DMF\Modules\ListingTypes;

defined( 'ABSPATH' ) || exit;

/**
 * A listing type is pure configuration — venues, hotels, DMCs, riads,
 * AV companies… are all instances of this shape. New types are created from
 * the admin UI, the REST API or WP-CLI without writing code.
 */
class ListingType {

	/**
	 * Constructor.
	 *
	 * @param string   $slug        URL-safe identifier, e.g. "convention-centers".
	 * @param string   $name        Plural label.
	 * @param string   $singular    Singular label.
	 * @param string   $icon        Icon name from the framework icon set.
	 * @param string   $description Short editorial description.
	 * @param string[] $field_groups Field group keys attached to this type.
	 * @param string[] $facets      Facet keys exposed in filtered search.
	 * @param string   $card        Card template variant (listing|venue|hotel|event|experience).
	 * @param bool     $builtin     Whether the type ships with the framework.
	 */
	public function __construct(
		public string $slug = '',
		public string $name = '',
		public string $singular = '',
		public string $icon = 'map-pin',
		public string $description = '',
		public array $field_groups = array(),
		public array $facets = array(),
		public string $card = 'listing',
		public bool $builtin = false
	) {}

	/**
	 * Hydrate from a stored array.
	 *
	 * @param array<string, mixed> $data Stored config.
	 */
	public static function from_array( array $data ): self {
		return new self(
			sanitize_title( (string) ( $data['slug'] ?? '' ) ),
			sanitize_text_field( (string) ( $data['name'] ?? '' ) ),
			sanitize_text_field( (string) ( $data['singular'] ?? ( $data['name'] ?? '' ) ) ),
			sanitize_key( (string) ( $data['icon'] ?? 'map-pin' ) ),
			sanitize_text_field( (string) ( $data['description'] ?? '' ) ),
			array_map( 'sanitize_key', (array) ( $data['field_groups'] ?? array() ) ),
			array_map( 'sanitize_key', (array) ( $data['facets'] ?? array() ) ),
			sanitize_key( (string) ( $data['card'] ?? 'listing' ) ),
			! empty( $data['builtin'] )
		);
	}

	/**
	 * Export for storage / REST.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return array(
			'slug'         => $this->slug,
			'name'         => $this->name,
			'singular'     => $this->singular,
			'icon'         => $this->icon,
			'description'  => $this->description,
			'field_groups' => $this->field_groups,
			'facets'       => $this->facets,
			'card'         => $this->card,
			'builtin'      => $this->builtin,
		);
	}
}
