<?php
/**
 * Admin field renderer.
 *
 * @package DMF
 */

namespace DMF\Modules\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Renders field inputs inside listing meta boxes. Markup is deliberately
 * plain WordPress admin vocabulary — no custom framework needed in wp-admin.
 */
class Renderer {

	/**
	 * Text-like input.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Current value.
	 * @param string               $name  Input name attribute.
	 */
	public static function text( array $field, mixed $value, string $name ): void {
		printf(
			'<input type="%s" class="regular-text" id="%s" name="%s" value="%s" placeholder="%s" />',
			esc_attr( in_array( $field['type'] ?? 'text', array( 'email', 'url' ), true ) ? $field['type'] : 'text' ),
			esc_attr( $name ),
			esc_attr( $name ),
			esc_attr( (string) $value ),
			esc_attr( (string) ( $field['placeholder'] ?? '' ) )
		);
	}

	/**
	 * Multi-line text.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Current value.
	 * @param string               $name  Input name attribute.
	 */
	public static function textarea( array $field, mixed $value, string $name ): void {
		printf(
			'<textarea class="large-text" rows="4" id="%1$s" name="%1$s">%2$s</textarea>',
			esc_attr( $name ),
			esc_textarea( (string) $value )
		);
	}

	/**
	 * Numeric input.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Current value.
	 * @param string               $name  Input name attribute.
	 */
	public static function number( array $field, mixed $value, string $name ): void {
		printf(
			'<input type="number" step="any" id="%1$s" name="%1$s" value="%2$s" />',
			esc_attr( $name ),
			esc_attr( (string) $value )
		);
	}

	/**
	 * Boolean toggle.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Current value.
	 * @param string               $name  Input name attribute.
	 */
	public static function toggle( array $field, mixed $value, string $name ): void {
		printf(
			'<label><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s /> %3$s</label>',
			esc_attr( $name ),
			checked( (bool) $value, true, false ),
			esc_html( (string) ( $field['label'] ?? '' ) )
		);
	}

	/**
	 * Single select.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Current value.
	 * @param string               $name  Input name attribute.
	 */
	public static function select( array $field, mixed $value, string $name ): void {
		printf( '<select id="%1$s" name="%1$s">', esc_attr( $name ) );
		printf( '<option value="">%s</option>', esc_html__( '— Select —', 'dmf' ) );
		foreach ( (array) ( $field['options'] ?? array() ) as $key => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( (string) $key ),
				selected( (string) $value, (string) $key, false ),
				esc_html( (string) $label )
			);
		}
		echo '</select>';
	}

	/**
	 * Checkbox set.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Current value.
	 * @param string               $name  Input name attribute.
	 */
	public static function multicheck( array $field, mixed $value, string $name ): void {
		$value = (array) $value;
		echo '<fieldset class="dmf-multicheck">';
		foreach ( (array) ( $field['options'] ?? array() ) as $key => $label ) {
			printf(
				'<label style="display:inline-block;margin:0 16px 6px 0"><input type="checkbox" name="%s[]" value="%s" %s /> %s</label>',
				esc_attr( $name ),
				esc_attr( (string) $key ),
				checked( in_array( (string) $key, array_map( 'strval', $value ), true ), true, false ),
				esc_html( (string) $label )
			);
		}
		echo '</fieldset>';
	}

	/**
	 * Latitude/longitude pair.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Current value.
	 * @param string               $name  Input name attribute.
	 */
	public static function geo( array $field, mixed $value, string $name ): void {
		$value = (array) $value;
		printf(
			'<input type="number" step="any" min="-90" max="90" placeholder="%s" name="%s[lat]" value="%s" /> ',
			esc_attr__( 'Latitude', 'dmf' ),
			esc_attr( $name ),
			esc_attr( (string) ( $value['lat'] ?? '' ) )
		);
		printf(
			'<input type="number" step="any" min="-180" max="180" placeholder="%s" name="%s[lng]" value="%s" />',
			esc_attr__( 'Longitude', 'dmf' ),
			esc_attr( $name ),
			esc_attr( (string) ( $value['lng'] ?? '' ) )
		);
	}

	/**
	 * Weekly opening hours.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Current value.
	 * @param string               $name  Input name attribute.
	 */
	public static function hours( array $field, mixed $value, string $name ): void {
		$value = (array) $value;
		$days  = array(
			'mon' => __( 'Monday', 'dmf' ),
			'tue' => __( 'Tuesday', 'dmf' ),
			'wed' => __( 'Wednesday', 'dmf' ),
			'thu' => __( 'Thursday', 'dmf' ),
			'fri' => __( 'Friday', 'dmf' ),
			'sat' => __( 'Saturday', 'dmf' ),
			'sun' => __( 'Sunday', 'dmf' ),
		);

		echo '<table class="dmf-hours">';
		foreach ( $days as $key => $label ) {
			printf(
				'<tr><th scope="row" style="text-align:left;padding-right:12px">%s</th><td><input type="text" placeholder="09:00–18:00" name="%s[%s]" value="%s" /></td></tr>',
				esc_html( $label ),
				esc_attr( $name ),
				esc_attr( $key ),
				esc_attr( (string) ( $value[ $key ] ?? '' ) )
			);
		}
		echo '</table>';
	}

	/**
	 * Single attachment (image or file) picker.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Current value.
	 * @param string               $name  Input name attribute.
	 */
	public static function attachment( array $field, mixed $value, string $name ): void {
		$id = absint( $value );
		printf(
			'<div class="dmf-attachment" data-type="%s"><input type="hidden" name="%s" value="%s" /><button type="button" class="button dmf-attachment-pick">%s</button> <span class="dmf-attachment-label">%s</span> <button type="button" class="button-link-delete dmf-attachment-clear" %s>%s</button></div>',
			esc_attr( 'image' === ( $field['type'] ?? '' ) ? 'image' : 'file' ),
			esc_attr( $name ),
			esc_attr( (string) ( $id ?: '' ) ),
			esc_html__( 'Select file', 'dmf' ),
			esc_html( $id ? basename( (string) get_attached_file( $id ) ) : '' ),
			$id ? '' : 'style="display:none"',
			esc_html__( 'Remove', 'dmf' )
		);
	}

	/**
	 * Multiple image picker.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Current value.
	 * @param string               $name  Input name attribute.
	 */
	public static function gallery( array $field, mixed $value, string $name ): void {
		$ids = array_filter( array_map( 'absint', (array) $value ) );
		printf(
			'<div class="dmf-gallery"><input type="hidden" name="%s" value="%s" /><button type="button" class="button dmf-gallery-pick">%s</button> <span class="dmf-gallery-count">%s</span></div>',
			esc_attr( $name ),
			esc_attr( implode( ',', $ids ) ),
			esc_html__( 'Select images', 'dmf' ),
			esc_html( sprintf( /* translators: %d: number of images. */ _n( '%d image', '%d images', count( $ids ), 'dmf' ), count( $ids ) ) )
		);
	}

	/**
	 * Simple key/value repeater rows.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Current value.
	 * @param string               $name  Input name attribute.
	 */
	public static function repeater( array $field, mixed $value, string $name ): void {
		$rows    = array_values( (array) $value );
		$rows[]  = array(); // Always one empty row to append with.
		$columns = (array) ( $field['columns'] ?? array( 'label' => __( 'Label', 'dmf' ), 'value' => __( 'Value', 'dmf' ) ) );

		echo '<table class="dmf-repeater widefat striped"><thead><tr>';
		foreach ( $columns as $label ) {
			printf( '<th>%s</th>', esc_html( (string) $label ) );
		}
		echo '</tr></thead><tbody>';
		foreach ( $rows as $i => $row ) {
			echo '<tr>';
			foreach ( array_keys( $columns ) as $column ) {
				printf(
					'<td><input type="text" name="%s[%d][%s]" value="%s" /></td>',
					esc_attr( $name ),
					(int) $i,
					esc_attr( (string) $column ),
					esc_attr( (string) ( $row[ $column ] ?? '' ) )
				);
			}
			echo '</tr>';
		}
		echo '</tbody></table>';
		printf( '<p class="description">%s</p>', esc_html__( 'Save the listing to append more rows.', 'dmf' ) );
	}
}
