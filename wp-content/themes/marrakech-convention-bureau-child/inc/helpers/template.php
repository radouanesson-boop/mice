<?php
/**
 * Template helpers.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a template part from /template-parts with data.
 *
 * Thin wrapper around get_template_part() that documents intent:
 *
 *     mcb_part( 'cards/card-venue', [ 'post_id' => 123 ] );
 *
 * Inside the part, data is available as $args (WP 5.5+).
 *
 * @param string $slug Path relative to template-parts/, without extension.
 * @param array  $args Data passed to the template part.
 */
function mcb_part( $slug, array $args = array() ) {
	get_template_part( 'template-parts/' . $slug, null, $args );
}

/**
 * Return a template part as a string (for Elementor widgets & shortcodes).
 *
 * @param string $slug Path relative to template-parts/.
 * @param array  $args Data passed to the template part.
 * @return string
 */
function mcb_part_html( $slug, array $args = array() ) {
	ob_start();
	mcb_part( $slug, $args );
	return (string) ob_get_clean();
}

/**
 * Echo an escaped, translated theme string. Convenience for parts.
 *
 * @param string $text Text to translate.
 */
function mcb_e( $text ) {
	echo esc_html( __( $text, 'mcb' ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
}

/**
 * Build a BEM class attribute from a base and modifiers.
 *
 *     mcb_bem( 'mcb-card', [ 'venue', is_sticky() ? 'featured' : null ] )
 *     → "mcb-card mcb-card--venue mcb-card--featured"
 *
 * @param string $block     BEM block name.
 * @param array  $modifiers Modifier names; null/empty values are skipped.
 * @return string Escaped class list.
 */
function mcb_bem( $block, array $modifiers = array() ) {
	$classes = array( $block );

	foreach ( array_filter( $modifiers ) as $modifier ) {
		$classes[] = $block . '--' . $modifier;
	}

	return esc_attr( implode( ' ', $classes ) );
}

/**
 * Responsive featured image with sensible defaults for card layouts.
 *
 * Uses native lazy loading and srcset (WordPress generates both), with a
 * lightweight placeholder wrapper so cards keep their aspect ratio while
 * images load.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Registered image size.
 * @param array  $attr    Extra attributes for the <img>.
 */
function mcb_thumbnail( $post_id, $size = 'mcb-card', array $attr = array() ) {
	if ( ! has_post_thumbnail( $post_id ) ) {
		echo '<span class="mcb-media__placeholder" aria-hidden="true">' . mcb_icon( 'image', array( 'echo' => false ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}

	$attr = wp_parse_args(
		$attr,
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
			'class'    => 'mcb-media__img',
		)
	);

	echo get_the_post_thumbnail( $post_id, $size, $attr ); // phpcs:ignore WordPress.Security.EscapeOutput
}
