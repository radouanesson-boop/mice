<?php
/**
 * Inline SVG icon system.
 *
 * Icons are individual SVG files in assets/svg/. They are inlined (not
 * requested over HTTP) so they inherit `currentColor`, cost zero extra
 * requests and can be styled entirely from CSS.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render (or return) an inline SVG icon.
 *
 *     mcb_icon( 'map-pin' );
 *     $svg = mcb_icon( 'calendar', [ 'echo' => false, 'class' => 'mcb-icon--lg' ] );
 *
 * @param string $name Icon file name without extension (assets/svg/{name}.svg).
 * @param array  $args {
 *     @type bool   $echo  Echo (default) or return.
 *     @type string $class Extra classes appended to .mcb-icon.
 *     @type string $label Accessible label; icon is aria-hidden when empty.
 * }
 * @return string|void
 */
function mcb_icon( $name, array $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'echo'  => true,
			'class' => '',
			'label' => '',
		)
	);

	$svg = mcb_icon_contents( $name );

	if ( '' === $svg ) {
		return $args['echo'] ? null : '';
	}

	$class = trim( 'mcb-icon mcb-icon--' . sanitize_html_class( $name ) . ' ' . $args['class'] );
	$a11y  = '' === $args['label']
		? 'aria-hidden="true" focusable="false"'
		: 'role="img" aria-label="' . esc_attr( $args['label'] ) . '"';

	// Inject class + a11y attributes into the root <svg>.
	$svg = preg_replace( '/<svg\b/', '<svg class="' . esc_attr( $class ) . '" ' . $a11y, $svg, 1 );

	if ( $args['echo'] ) {
		echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput -- sanitised static asset.
		return;
	}

	return $svg;
}

/**
 * Read and cache an icon file's contents.
 *
 * @param string $name Icon name.
 * @return string SVG markup or empty string.
 */
function mcb_icon_contents( $name ) {
	static $cache = array();

	if ( isset( $cache[ $name ] ) ) {
		return $cache[ $name ];
	}

	$file = MCB_DIR . '/assets/svg/' . sanitize_file_name( $name ) . '.svg';

	$cache[ $name ] = file_exists( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions

	if ( '' === $cache[ $name ] && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf( '[MCB] Missing icon: %s', $name ) ); // phpcs:ignore
	}

	return $cache[ $name ];
}
