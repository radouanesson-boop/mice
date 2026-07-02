<?php
/**
 * Listing field meta boxes.
 *
 * @package DMF
 */

namespace DMF\Modules\Fields;

use DMF\Modules\Listings\PostTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Renders one meta box per field group for the listing's type and persists
 * values with nonce + capability checks and per-type sanitization.
 */
class MetaBox {

	private const NONCE_ACTION = 'dmf_save_fields';
	private const NONCE_NAME   = 'dmf_fields_nonce';

	/**
	 * Constructor.
	 *
	 * @param FieldRegistry $fields Field registry.
	 * @param FieldTypes    $types  Field type registry.
	 */
	public function __construct(
		private FieldRegistry $fields,
		private FieldTypes $types
	) {}

	/**
	 * Attach hooks.
	 */
	public function hooks(): void {
		add_action( 'add_meta_boxes_' . PostTypes::LISTING, array( $this, 'register_boxes' ) );
		add_action( 'save_post_' . PostTypes::LISTING, array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Register a meta box per field group of the listing's current type.
	 *
	 * @param \WP_Post $post Listing being edited.
	 */
	public function register_boxes( \WP_Post $post ): void {
		$type_slug = PostTypes::listing_type_slug( $post->ID );

		$groups = $type_slug ? $this->fields->groups_for_type( $type_slug ) : array();

		if ( ! $groups ) {
			add_meta_box(
				'dmf-fields-empty',
				__( 'Listing Details', 'dmf' ),
				static function () {
					printf(
						'<p>%s</p>',
						esc_html__( 'Assign a listing type (right sidebar), then save to load its field groups.', 'dmf' )
					);
				},
				PostTypes::LISTING
			);
			return;
		}

		foreach ( $groups as $key => $group ) {
			add_meta_box(
				'dmf-fields-' . $key,
				(string) ( $group['title'] ?? $key ),
				function () use ( $group ) {
					$this->render_group( $group );
				},
				PostTypes::LISTING,
				'normal',
				'default'
			);
		}
	}

	/**
	 * Render a group's fields.
	 *
	 * @param array<string, mixed> $group Group definition.
	 */
	private function render_group( array $group ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		echo '<table class="form-table" role="presentation">';

		foreach ( (array) ( $group['fields'] ?? array() ) as $field ) {
			$field = (array) $field;
			$key   = (string) ( $field['key'] ?? '' );
			$type  = $this->types->get( (string) ( $field['type'] ?? 'text' ) );

			if ( '' === $key || ! $type ) {
				continue;
			}

			$name  = 'dmf_fields[' . $key . ']';
			$value = FieldRegistry::value( get_the_ID(), $key );

			printf(
				'<tr><th scope="row"><label for="%s">%s</label></th><td>',
				esc_attr( $name ),
				esc_html( (string) ( $field['label'] ?? $key ) )
			);

			( $type['render'] )( $field, $value, $name );

			if ( ! empty( $field['description'] ) ) {
				printf( '<p class="description">%s</p>', esc_html( (string) $field['description'] ) );
			}

			echo '</td></tr>';
		}

		echo '</table>';
	}

	/**
	 * Persist submitted field values.
	 *
	 * @param int      $post_id Listing ID.
	 * @param \WP_Post $post    Listing post.
	 */
	public function save( int $post_id, \WP_Post $post ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_key( (string) wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$submitted = isset( $_POST['dmf_fields'] ) ? (array) wp_unslash( $_POST['dmf_fields'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized per field type below.

		$type_slug = PostTypes::listing_type_slug( $post_id );
		if ( ! $type_slug ) {
			return;
		}

		foreach ( $this->fields->fields_for_type( $type_slug ) as $key => $field ) {
			$raw   = $submitted[ $key ] ?? '';
			$field = (array) $field;

			// Gallery submits a comma-separated id string.
			if ( 'gallery' === ( $field['type'] ?? '' ) && is_string( $raw ) ) {
				$raw = array_filter( explode( ',', $raw ) );
			}

			FieldRegistry::update( $post_id, $key, $this->types->sanitize( $raw, $field ) );
		}

		/**
		 * Fires after a listing's fields have been saved.
		 *
		 * @param int    $post_id   Listing ID.
		 * @param string $type_slug Listing type slug.
		 */
		do_action( 'dmf/fields/saved', $post_id, $type_slug );
	}
}
