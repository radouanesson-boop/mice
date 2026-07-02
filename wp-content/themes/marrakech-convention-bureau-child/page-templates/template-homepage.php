<?php
/**
 * Template Name: MCB Homepage
 * Template Post Type: page
 *
 * PHP homepage implementing design board 1A ("Editorial Luxe") section by
 * section. In production the client can instead build the homepage with
 * Elementor (docs/ELEMENTOR-GUIDE.md) — if the page has Elementor content,
 * that always wins.
 *
 * Section order (board 1A):
 *   nav → hero → trust strip → why marrakech (+strengths) → convention
 *   bureau services → venues → experiences → success (stats + quote) →
 *   insights → partners → final CTA → footer.
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
	// 1. Hero — split layout with frosted stats card.
	mcb_part( 'hero/hero-home' );

	// 2. Trust strip — reference events.
	mcb_part( 'sections/section-trust' );

	// 3. Why Marrakech — lead + chips + strengths grid.
	mcb_part( 'sections/section-why' );

	// 4. The Convention Bureau — numbered services.
	mcb_part( 'sections/section-services' );

	// 5. Venues.
	mcb_part(
		'sections/section-listings',
		array(
			'kicker'    => __( 'Venues', 'mcb' ),
			'title'     => __( 'Where your delegates gather', 'mcb' ),
			'kind'      => 'venue',
			'count'     => 3,
			'more_text' => __( 'View all venues', 'mcb' ),
			'more_url'  => home_url( '/venues/' ),
			'footnote'  => __( '° Indicative capacities — confirmed on enquiry.', 'mcb' ),
		)
	);

	// 6. Signature experiences — overlay cards on paper.
	mcb_part(
		'sections/section-listings',
		array(
			'kicker' => __( 'Signature Experiences', 'mcb' ),
			'title'  => __( 'The moments they’ll talk about for years', 'mcb' ),
			'kind'   => 'experience',
			'count'  => 6,
			'paper'  => true,
		)
	);

	// 7. Success — reference stats + featured pull-quote.
	mcb_part( 'sections/section-stats' );

	// 8. Insights — journal cards.
	mcb_part( 'sections/section-news' );

	// 9. Partners strip.
	mcb_part( 'sections/section-partners' );

	// 10. Final CTA.
	mcb_part( 'sections/section-cta' );
}

get_footer();
