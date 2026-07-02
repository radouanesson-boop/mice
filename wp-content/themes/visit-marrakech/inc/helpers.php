<?php
/**
 * Theme helpers — the design-system rendering API.
 *
 * Template parts keep the `mcb_*` helper names (the design system is the
 * "Marrakech Convention Bureau" visual language); listing DATA helpers are
 * thin shims over the DMF Core developer API, each guarded so the theme
 * renders even when the framework is inactive.
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a template part from /template-parts with data ($args inside).
 */
function mcb_part( string $slug, array $args = array() ): void {
	get_template_part( 'template-parts/' . $slug, null, $args );
}

/**
 * Return a template part as a string.
 */
function mcb_part_html( string $slug, array $args = array() ): string {
	ob_start();
	mcb_part( $slug, $args );
	return (string) ob_get_clean();
}

/**
 * BEM class list helper.
 *
 * @param array<int, string|null> $modifiers Modifier names; empty skipped.
 */
function mcb_bem( string $block, array $modifiers = array() ): string {
	$classes = array( $block );

	foreach ( array_filter( $modifiers ) as $modifier ) {
		$classes[] = $block . '--' . $modifier;
	}

	return esc_attr( implode( ' ', $classes ) );
}

/**
 * Inline SVG icon from assets/svg (inherits currentColor).
 *
 * @param array{echo?: bool, class?: string, label?: string} $args Options.
 */
function mcb_icon( string $name, array $args = array() ) {
	$args = wp_parse_args( $args, array( 'echo' => true, 'class' => '', 'label' => '' ) );

	static $cache = array();

	if ( ! isset( $cache[ $name ] ) ) {
		$file           = VM_DIR . '/assets/svg/' . sanitize_file_name( $name ) . '.svg';
		$cache[ $name ] = is_readable( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
	}

	if ( '' === $cache[ $name ] ) {
		return $args['echo'] ? null : '';
	}

	$class = trim( 'mcb-icon mcb-icon--' . sanitize_html_class( $name ) . ' ' . $args['class'] );
	$a11y  = '' === $args['label']
		? 'aria-hidden="true" focusable="false"'
		: 'role="img" aria-label="' . esc_attr( $args['label'] ) . '"';

	$svg = preg_replace( '/<svg\b/', '<svg class="' . esc_attr( $class ) . '" ' . $a11y, $cache[ $name ], 1 );

	if ( $args['echo'] ) {
		echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput -- static theme asset.
		return null;
	}

	return $svg;
}

/**
 * Responsive featured image for cards.
 */
function mcb_thumbnail( int $post_id, string $size = 'mcb-card', array $attr = array() ): void {
	if ( ! has_post_thumbnail( $post_id ) ) {
		echo '<span class="mcb-media__placeholder" aria-hidden="true">' . mcb_icon( 'image', array( 'echo' => false ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}

	$attr = wp_parse_args( $attr, array( 'loading' => 'lazy', 'decoding' => 'async', 'class' => 'mcb-media__img' ) );

	echo get_the_post_thumbnail( $post_id, $size, $attr ); // phpcs:ignore WordPress.Security.EscapeOutput
}

// ---------------------------------------------------------------------------
// Listing data shims → DMF Core API (guarded).
// ---------------------------------------------------------------------------

/**
 * Active listing post types.
 *
 * @return string[]
 */
function mcb_listing_post_types(): array {
	return function_exists( 'dmf_listing_types' ) ? dmf_listing_types() : array();
}

/**
 * Whether a post is a listing.
 */
function mcb_is_listing( $post = null ): bool {
	return in_array( get_post_type( $post ), mcb_listing_post_types(), true );
}

/**
 * Card kind for a listing.
 */
function mcb_listing_kind( $post = null ): string {
	$post_id = is_object( $post ) ? $post->ID : ( $post ?: get_the_ID() );

	return function_exists( 'dmf_listing_kind' ) ? dmf_listing_kind( (int) $post_id ) : 'listing';
}

/**
 * Listing field value via the framework.
 */
function mcb_listing_meta( string $key, $post = null ) {
	$post_id = is_object( $post ) ? $post->ID : ( $post ?: get_the_ID() );

	return function_exists( 'dmf_get_field' ) ? dmf_get_field( $key, (int) $post_id ) : '';
}

/**
 * Average review rating (denormalized by the Reviews module).
 */
function mcb_listing_rating( $post = null ): float {
	$post_id = is_object( $post ) ? $post->ID : ( $post ?: get_the_ID() );
	$value   = get_post_meta( (int) $post_id, '_dmf_rating', true );

	return is_numeric( $value ) ? round( (float) $value, 1 ) : 0.0;
}

/**
 * Primary category term (card badge).
 */
function mcb_listing_primary_term( $post = null ): ?WP_Term {
	$post = get_post( $post );

	if ( ! $post ) {
		return null;
	}

	$terms = get_the_terms( $post, 'dmf_category' );

	return ( is_array( $terms ) && $terms ) ? $terms[0] : null;
}

/**
 * Listings query via the framework.
 */
function mcb_listing_query( array $args = array() ): WP_Query {
	if ( function_exists( 'dmf_listing_query' ) ) {
		return dmf_listing_query( $args );
	}

	return new WP_Query( array( 'post__in' => array( 0 ) ) ); // Empty, framework inactive.
}

/**
 * Breadcrumbs part (delegates to the framework's schema-ready trail).
 */
function mcb_breadcrumbs(): void {
	mcb_part( 'components/breadcrumbs' );
}
