<?php
/**
 * Breadcrumbs — SEO plugin first (single schema source), then the
 * framework's schema-ready trail.
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

if ( is_front_page() ) {
	return;
}

echo '<nav class="mcb-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'vm' ) . '">';

if ( function_exists( 'yoast_breadcrumb' ) ) {
	yoast_breadcrumb( '<div class="mcb-breadcrumbs__trail">', '</div>' );
} elseif ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
	rank_math_the_breadcrumbs();
} elseif ( function_exists( 'dmf_breadcrumbs' ) ) {
	$vm_trail = dmf_breadcrumbs();
	$vm_last  = count( $vm_trail ) - 1;

	echo '<div class="mcb-breadcrumbs__trail">';

	foreach ( $vm_trail as $vm_i => $vm_crumb ) {
		if ( $vm_i > 0 ) {
			echo '<span class="mcb-breadcrumbs__sep" aria-hidden="true">/</span>';
		}

		if ( $vm_i < $vm_last && $vm_crumb['url'] ) {
			printf( '<a href="%s">%s</a>', esc_url( $vm_crumb['url'] ), esc_html( $vm_crumb['name'] ) );
		} else {
			printf( '<span aria-current="page">%s</span>', esc_html( $vm_crumb['name'] ) );
		}
	}

	echo '</div>';
}

echo '</nav>';
