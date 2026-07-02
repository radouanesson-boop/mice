<?php
/**
 * Layout-level hooks: page hero, breadcrumbs, archive wrappers.
 *
 * These attach to generic WordPress hooks so they work regardless of which
 * template (Brikk's, Routiz Blade, or ours) renders the main content.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inner page hero (title + breadcrumbs) on non-Elementor, non-front pages.
 *
 * Elementor-built pages usually design their own hero, so we skip any page
 * built with Elementor to avoid a double header.
 */
function mcb_maybe_page_hero() {
	if ( is_front_page() || is_singular( mcb_listing_post_types() ) ) {
		return; // Front page and single listings design their own hero.
	}

	if ( class_exists( '\Elementor\Plugin' ) && is_singular() ) {
		$document = \Elementor\Plugin::$instance->documents->get( get_the_ID() );

		if ( $document && $document->is_built_with_elementor() ) {
			return;
		}
	}

	mcb_part( 'hero/hero-page' );
}
add_action( 'mcb/before_content', 'mcb_maybe_page_hero' );

/**
 * Breadcrumbs — delegates to whichever SEO plugin is active, in order of
 * preference, so schema.org BreadcrumbList markup comes from one source.
 */
function mcb_breadcrumbs() {
	mcb_part( 'components/breadcrumbs' );
}
