<?php
/**
 * Field type registry.
 *
 * @package DMF
 */

namespace DMF\Modules\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Each field type declares how to sanitize, render (admin) and schema-type
 * its values. Extensions add types through the `dmf/fields/types` filter —
 * a "weather-station reading" field is a filter away, no core changes.
 */
class FieldTypes {

	/**
	 * Runtime cache.
	 *
	 * @var array<string, array>|null
	 */
	private ?array $types = null;

	/**
	 * All field types.
	 *
	 * @return array<string, array{schema: string, sanitize: callable, render: callable}>
	 */
	public function all(): array {
		if ( null !== $this->types ) {
			return $this->types;
		}

		$types = array(
			'text'       => array(
				'schema'   => 'string',
				'sanitize' => static fn( $v ) => sanitize_text_field( (string) $v ),
				'render'   => array( Renderer::class, 'text' ),
			),
			'textarea'   => array(
				'schema'   => 'string',
				'sanitize' => static fn( $v ) => sanitize_textarea_field( (string) $v ),
				'render'   => array( Renderer::class, 'textarea' ),
			),
			'email'      => array(
				'schema'   => 'string',
				'sanitize' => static fn( $v ) => sanitize_email( (string) $v ),
				'render'   => array( Renderer::class, 'text' ),
			),
			'url'        => array(
				'schema'   => 'string',
				'sanitize' => static fn( $v ) => esc_url_raw( (string) $v ),
				'render'   => array( Renderer::class, 'text' ),
			),
			'number'     => array(
				'schema'   => 'number',
				'sanitize' => static fn( $v ) => '' === $v ? '' : ( is_numeric( $v ) ? 0 + $v : '' ),
				'render'   => array( Renderer::class, 'number' ),
			),
			'toggle'     => array(
				'schema'   => 'boolean',
				'sanitize' => static fn( $v ) => (bool) $v,
				'render'   => array( Renderer::class, 'toggle' ),
			),
			'select'     => array(
				'schema'   => 'string',
				'sanitize' => static function ( $v, array $field ) {
					$v = sanitize_text_field( (string) $v );
					$options = (array) ( $field['options'] ?? array() );
					return ( '' === $v || isset( $options[ $v ] ) ) ? $v : '';
				},
				'render'   => array( Renderer::class, 'select' ),
			),
			'multicheck' => array(
				'schema'   => 'array',
				'sanitize' => static function ( $v, array $field ) {
					$options = (array) ( $field['options'] ?? array() );
					return array_values( array_intersect( array_map( 'sanitize_text_field', (array) $v ), array_keys( $options ) ) );
				},
				'render'   => array( Renderer::class, 'multicheck' ),
			),
			'geo'        => array(
				'schema'   => 'object',
				'sanitize' => static function ( $v ) {
					$v   = (array) $v;
					$lat = isset( $v['lat'] ) && is_numeric( $v['lat'] ) ? (float) $v['lat'] : null;
					$lng = isset( $v['lng'] ) && is_numeric( $v['lng'] ) ? (float) $v['lng'] : null;
					if ( null === $lat || null === $lng || abs( $lat ) > 90 || abs( $lng ) > 180 ) {
						return array();
					}
					return array( 'lat' => $lat, 'lng' => $lng );
				},
				'render'   => array( Renderer::class, 'geo' ),
			),
			'hours'      => array(
				'schema'   => 'object',
				'sanitize' => static function ( $v ) {
					$clean = array();
					foreach ( (array) $v as $day => $range ) {
						$day = sanitize_key( (string) $day );
						if ( in_array( $day, array( 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ), true ) ) {
							$clean[ $day ] = sanitize_text_field( (string) $range );
						}
					}
					return array_filter( $clean );
				},
				'render'   => array( Renderer::class, 'hours' ),
			),
			'image'      => array(
				'schema'   => 'integer',
				'sanitize' => static fn( $v ) => absint( $v ),
				'render'   => array( Renderer::class, 'attachment' ),
			),
			'file'       => array(
				'schema'   => 'integer',
				'sanitize' => static fn( $v ) => absint( $v ),
				'render'   => array( Renderer::class, 'attachment' ),
			),
			'gallery'    => array(
				'schema'   => 'array',
				'sanitize' => static fn( $v ) => array_values( array_filter( array_map( 'absint', (array) $v ) ) ),
				'render'   => array( Renderer::class, 'gallery' ),
			),
			'repeater'   => array(
				'schema'   => 'array',
				'sanitize' => static function ( $v ) {
					$clean = array();
					foreach ( (array) $v as $row ) {
						$row = array_map( static fn( $cell ) => sanitize_text_field( (string) $cell ), (array) $row );
						if ( array_filter( $row ) ) {
							$clean[] = $row;
						}
					}
					return $clean;
				},
				'render'   => array( Renderer::class, 'repeater' ),
			),
		);

		/**
		 * Filter available field types.
		 *
		 * @param array<string, array> $types Field type definitions.
		 */
		$this->types = (array) apply_filters( 'dmf/fields/types', $types );

		return $this->types;
	}

	/**
	 * One field type definition.
	 *
	 * @param string $type Type key.
	 * @return array{schema: string, sanitize: callable, render: callable}|null
	 */
	public function get( string $type ): ?array {
		return $this->all()[ $type ] ?? null;
	}

	/**
	 * Sanitize a raw value against its field definition.
	 *
	 * @param mixed                $value Raw value.
	 * @param array<string, mixed> $field Field definition (needs `type`).
	 */
	public function sanitize( mixed $value, array $field ): mixed {
		$type = $this->get( (string) ( $field['type'] ?? 'text' ) );
		if ( ! $type ) {
			return sanitize_text_field( (string) $value );
		}
		return ( $type['sanitize'] )( $value, $field );
	}
}
