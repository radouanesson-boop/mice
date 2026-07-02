<?php
/**
 * Breadcrumbs — delegate to the active SEO plugin so BreadcrumbList schema
 * has a single source of truth. Minimal fallback otherwise.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

if ( is_front_page() ) {
	return;
}

echo '<nav class="mcb-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'mcb' ) . '">';

if ( function_exists( 'yoast_breadcrumb' ) ) {
	yoast_breadcrumb( '<div class="mcb-breadcrumbs__trail">', '</div>' );
} elseif ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
	rank_math_the_breadcrumbs();
} elseif ( function_exists( 'bcn_display' ) ) {
	echo '<div class="mcb-breadcrumbs__trail">';
	bcn_display();
	echo '</div>';
} else {
	echo '<div class="mcb-breadcrumbs__trail">';
	printf( '<a href="%s">%s</a>', esc_url( home_url( '/' ) ), esc_html__( 'Home', 'mcb' ) );
	echo '<span class="mcb-breadcrumbs__sep" aria-hidden="true">/</span>';
	echo '<span aria-current="page">' . esc_html( wp_strip_all_tags( single_post_title( '', false ) ?: get_the_archive_title() ) ) . '</span>';
	echo '</div>';
}

echo '</nav>';
