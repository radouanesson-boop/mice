<?php
/**
 * Field definition + sanitization.
 *
 * @package DMF
 */

namespace DMF\Modules\Listings\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * One custom field. Fields are configuration, not code:
 *
 *   [ 'key' => 'capacity', 'label' => 'Capacity', 'type' => 'number' ]
 *
 * Supported types: text, textarea, number, email, url, tel, select,
 * multiselect, checkbox, date, time, gallery (attachment ids), file
 * (attachment id), geo (lat/lng pair), repeater-lite (newline list),
 * hours (7-row opening hours), social (network => url map).
 *
 * Meta keys are stored as "_dmf_{key}" and registered with
 * register_post_meta() so they're REST-visible and block-editor friendly.
 */
final class Field {

	public function __construct(
		public readonly string $key,
		public readonly string $label,
		public readonly string $type = 'text',
		public readonly array $options = array(),
		public readonly string $description = '',
		public readonly bool $searchable = false,
	) {}

	/**
	 * @param array<string, mixed> $config Raw field config.
	 */
	public static function from_array( array $config ): self {
		return new self(
			sanitize_key( $config['key'] ?? '' ),
			(string) ( $config['label'] ?? '' ),
			sanitize_key( $config['type'] ?? 'text' ),
			(array) ( $config['options'] ?? array() ),
			(string) ( $config['description'] ?? '' ),
			(bool) ( $config['searchable'] ?? false ),
		);
	}

	/**
	 * The stored meta key.
	 */
	public function meta_key(): string {
		return '_dmf_' . $this->key;
	}

	/**
	 * Sanitize a submitted value according to the field type.
	 */
	public function sanitize( mixed $value ): mixed {
		return match ( $this->type ) {
			'number'       => '' === $value ? '' : (string) floatval( $value ),
			'email'        => sanitize_email( (string) $value ),
			'url'          => esc_url_raw( (string) $value ),
			'textarea'     => sanitize_textarea_field( (string) $value ),
			'checkbox'     => $value ? '1' : '',
			'date'         => preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $value ) ? (string) $value : '',
			'time'         => preg_match( '/^\d{2}:\d{2}$/', (string) $value ) ? (string) $value : '',
			'select'       => $this->sanitize_choice( (string) $value ),
			'multiselect'  => array_values( array_filter( array_map( fn( $v ) => $this->sanitize_choice( (string) $v ), (array) $value ) ) ),
			'gallery'      => array_values( array_filter( array_map( 'absint', is_array( $value ) ? $value : explode( ',', (string) $value ) ) ) ),
			'file'         => absint( $value ),
			'geo'          => $this->sanitize_geo( $value ),
			'repeater-lite' => sanitize_textarea_field( (string) $value ),
			'hours', 'social' => array_map( 'sanitize_text_field', array_map( 'strval', (array) $value ) ),
			default        => sanitize_text_field( (string) $value ),
		};
	}

	private function sanitize_choice( string $value ): string {
		$allowed = array_map( 'strval', array_keys( $this->options ) ?: array_values( $this->options ) );

		return in_array( $value, $allowed, true ) ? $value : '';
	}

	/**
	 * @param mixed $value Expected [ 'lat' => float, 'lng' => float ].
	 * @return array{lat: string, lng: string}
	 */
	private function sanitize_geo( mixed $value ): array {
		$value = (array) $value;
		$lat   = isset( $value['lat'] ) && is_numeric( $value['lat'] ) ? max( -90, min( 90, (float) $value['lat'] ) ) : '';
		$lng   = isset( $value['lng'] ) && is_numeric( $value['lng'] ) ? max( -180, min( 180, (float) $value['lng'] ) ) : '';

		return array(
			'lat' => '' === $lat ? '' : (string) $lat,
			'lng' => '' === $lng ? '' : (string) $lng,
		);
	}
}
