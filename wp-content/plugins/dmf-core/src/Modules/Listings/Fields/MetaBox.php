<?php
/**
 * Field-group meta box renderer + saver.
 *
 * @package DMF
 */

namespace DMF\Modules\Listings\Fields;

use DMF\Modules\Listings\ListingType;

defined( 'ABSPATH' ) || exit;

/**
 * Renders each listing type's field groups as classic meta boxes (works in
 * both the block editor and classic editor) and saves them with full
 * nonce / capability / sanitization discipline.
 */
final class MetaBox {

	private const NONCE = 'dmf_fields_nonce';

	public function hooks(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Register one meta box per field group of the edited listing type.
	 */
	public function add_boxes( string $post_type, \WP_Post $post ): void {
		$type = dmf()->module( 'listings' )?->registry()->get( $post_type );

		if ( ! $type ) {
			return;
		}

		foreach ( $type->fields as $group_key => $group ) {
			add_meta_box(
				'dmf-group-' . sanitize_key( $group_key ),
				esc_html( $group['label'] ?? ucfirst( $group_key ) ),
				function ( \WP_Post $post ) use ( $group ) {
					$this->render_group( $post, (array) ( $group['fields'] ?? array() ) );
				},
				$post_type,
				'normal',
				'default'
			);
		}
	}

	/**
	 * @param array<int, array> $fields Field configs.
	 */
	private function render_group( \WP_Post $post, array $fields ): void {
		wp_nonce_field( self::NONCE, self::NONCE );

		echo '<table class="form-table dmf-fields" role="presentation">';

		foreach ( $fields as $config ) {
			$field = Field::from_array( (array) $config );

			if ( '' === $field->key ) {
				continue;
			}

			$value = get_post_meta( $post->ID, $field->meta_key(), true );
			$id    = 'dmf-' . $field->key;

			echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field->label ) . '</label></th><td>';
			$this->render_control( $field, $value, $id );

			if ( $field->description ) {
				echo '<p class="description">' . esc_html( $field->description ) . '</p>';
			}

			echo '</td></tr>';
		}

		echo '</table>';
	}

	private function render_control( Field $field, mixed $value, string $id ): void {
		$name = 'dmf_fields[' . esc_attr( $field->key ) . ']';

		switch ( $field->type ) {
			case 'textarea':
			case 'repeater-lite':
				printf(
					'<textarea class="large-text" rows="4" id="%s" name="%s">%s</textarea>',
					esc_attr( $id ),
					$name, // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
					esc_textarea( (string) $value )
				);
				break;

			case 'checkbox':
				printf(
					'<label><input type="checkbox" id="%s" name="%s" value="1" %s> %s</label>',
					esc_attr( $id ),
					$name, // phpcs:ignore WordPress.Security.EscapeOutput
					checked( (string) $value, '1', false ),
					esc_html__( 'Yes', 'dmf' )
				);
				break;

			case 'select':
			case 'multiselect':
				$multiple = 'multiselect' === $field->type;
				printf(
					'<select id="%s" name="%s%s" %s>',
					esc_attr( $id ),
					$name, // phpcs:ignore WordPress.Security.EscapeOutput
					$multiple ? '[]' : '',
					$multiple ? 'multiple size="5"' : ''
				);

				if ( ! $multiple ) {
					echo '<option value="">' . esc_html__( '— Select —', 'dmf' ) . '</option>';
				}

				foreach ( $field->options as $opt_value => $opt_label ) {
					$opt_value = is_int( $opt_value ) ? (string) $opt_label : (string) $opt_value;
					$selected  = $multiple
						? in_array( $opt_value, (array) $value, true )
						: (string) $value === $opt_value;

					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $opt_value ),
						selected( $selected, true, false ),
						esc_html( (string) $opt_label )
					);
				}

				echo '</select>';
				break;

			case 'geo':
				$geo = (array) $value;
				printf(
					'<input type="text" inputmode="decimal" placeholder="%s" name="%s[lat]" value="%s" class="regular-text" style="max-width:10em"> ',
					esc_attr__( 'Latitude', 'dmf' ),
					$name, // phpcs:ignore WordPress.Security.EscapeOutput
					esc_attr( (string) ( $geo['lat'] ?? '' ) )
				);
				printf(
					'<input type="text" inputmode="decimal" placeholder="%s" name="%s[lng]" value="%s" class="regular-text" style="max-width:10em">',
					esc_attr__( 'Longitude', 'dmf' ),
					$name, // phpcs:ignore WordPress.Security.EscapeOutput
					esc_attr( (string) ( $geo['lng'] ?? '' ) )
				);
				break;

			case 'gallery':
				printf(
					'<input type="text" class="regular-text" id="%s" name="%s" value="%s" placeholder="%s">',
					esc_attr( $id ),
					$name, // phpcs:ignore WordPress.Security.EscapeOutput
					esc_attr( implode( ',', (array) $value ) ),
					esc_attr__( 'Attachment IDs, comma-separated', 'dmf' )
				);
				break;

			default:
				$input_type = in_array( $field->type, array( 'number', 'email', 'url', 'tel', 'date', 'time' ), true ) ? $field->type : 'text';
				printf(
					'<input type="%s" class="regular-text" id="%s" name="%s" value="%s">',
					esc_attr( $input_type ),
					esc_attr( $id ),
					$name, // phpcs:ignore WordPress.Security.EscapeOutput
					esc_attr( is_scalar( $value ) ? (string) $value : '' )
				);
		}
	}

	/**
	 * Persist submitted field values.
	 */
	public function save( int $post_id, \WP_Post $post ): void {
		if (
			! isset( $_POST[ self::NONCE ] )
			|| ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE ] ), self::NONCE )
			|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| ! current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		$type = dmf()->module( 'listings' )?->registry()->get( $post->post_type );

		if ( ! $type || ! isset( $_POST['dmf_fields'] ) ) {
			return;
		}

		$submitted = wp_unslash( (array) $_POST['dmf_fields'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- per-field below.

		foreach ( $type->fields as $group ) {
			foreach ( (array) ( $group['fields'] ?? array() ) as $config ) {
				$field = Field::from_array( (array) $config );

				if ( '' === $field->key ) {
					continue;
				}

				if ( ! array_key_exists( $field->key, $submitted ) ) {
					// Unchecked checkboxes / absent multiselects arrive empty.
					if ( in_array( $field->type, array( 'checkbox', 'multiselect' ), true ) ) {
						delete_post_meta( $post_id, $field->meta_key() );
					}
					continue;
				}

				$clean = $field->sanitize( $submitted[ $field->key ] );

				if ( '' === $clean || array() === $clean ) {
					delete_post_meta( $post_id, $field->meta_key() );
				} else {
					update_post_meta( $post_id, $field->meta_key(), $clean );
				}
			}
		}

		/**
		 * Fires after a listing's fields were saved.
		 *
		 * @param int      $post_id Listing ID.
		 * @param \WP_Post $post    Listing post.
		 */
		do_action( 'dmf/listing_saved', $post_id, $post );
	}
}
