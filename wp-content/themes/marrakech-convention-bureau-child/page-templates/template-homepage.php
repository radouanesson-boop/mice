<?php
/**
 * Template Name: MCB Homepage
 * Template Post Type: page
 *
 * PHP fallback homepage. In production the homepage should be built with
 * Elementor (see docs/ELEMENTOR-GUIDE.md) using the MCB widgets — this
 * template exists so the site is complete before Elementor templates are
 * imported, and as living documentation of the section order.
 *
 * If the page HAS Elementor content, we simply render it (the client's
 * edits always win).
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mcb_built_with_elementor = class_exists( '\Elementor\Plugin' )
	&& \Elementor\Plugin::$instance->documents->get( get_the_ID() )
	&& \Elementor\Plugin::$instance->documents->get( get_the_ID() )->is_built_with_elementor();

if ( $mcb_built_with_elementor ) {
	while ( have_posts() ) {
		the_post();
		the_content();
	}
} else {
	// 1. Hero with integrated search.
	mcb_part( 'hero/hero-home', array( 'show_search' => true ) );

	// 2. Key statistics.
	mcb_part( 'sections/section-stats' );

	// 3. Featured venues.
	mcb_part(
		'sections/section-listings',
		array(
			'kicker' => __( 'Venues', 'mcb' ),
			'title'  => __( 'Exceptional venues for every format', 'mcb' ),
			'kind'   => 'venue',
			'count'  => 6,
		)
	);

	// 4. Hotels.
	mcb_part(
		'sections/section-listings',
		array(
			'kicker' => __( 'Stay', 'mcb' ),
			'title'  => __( 'Hotels & resorts built for delegations', 'mcb' ),
			'kind'   => 'hotel',
			'count'  => 3,
		)
	);

	// 5. Experiences.
	mcb_part(
		'sections/section-listings',
		array(
			'kicker' => __( 'Experiences', 'mcb' ),
			'title'  => __( 'Incentives they will never forget', 'mcb' ),
			'kind'   => 'experience',
			'count'  => 4,
		)
	);

	// 6. Why Marrakech (testimonials).
	mcb_part( 'sections/section-testimonials' );

	// 7. Upcoming events.
	mcb_part(
		'sections/section-listings',
		array(
			'kicker' => __( 'Calendar', 'mcb' ),
			'title'  => __( 'Upcoming events & congresses', 'mcb' ),
			'kind'   => 'event',
			'count'  => 3,
		)
	);

	// 8. News.
	mcb_part( 'sections/section-news' );

	// 9. Partners.
	mcb_part( 'sections/section-partners' );

	// 10. Closing CTA.
	mcb_part( 'sections/section-cta' );
}

get_footer();
