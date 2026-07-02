<?php
/**
 * Homepage — design board 1A ("Editorial Luxe"), section by section.
 * Every section reads Customizer mods / vm filters / live DMF listings.
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

get_header();

// 1. Hero — split layout with frosted stats card.
mcb_part( 'hero/hero-home' );

// 2. Trust strip.
mcb_part( 'sections/section-trust' );

// 3. Why Marrakech + strengths grid.
mcb_part( 'sections/section-why' );

// 4. The Convention Bureau — numbered services.
mcb_part( 'sections/section-services' );

// 5. Venues.
mcb_part(
	'sections/section-listings',
	array(
		'kicker'    => __( 'Venues', 'vm' ),
		'title'     => __( 'Where your delegates gather', 'vm' ),
		'kind'      => 'venue',
		'count'     => 3,
		'more_text' => __( 'View all venues', 'vm' ),
		'more_url'  => get_post_type_archive_link( 'dmf_venue' ) ?: home_url( '/venues/' ),
		'footnote'  => __( '° Indicative capacities — confirmed on enquiry.', 'vm' ),
	)
);

// 6. Signature experiences.
mcb_part(
	'sections/section-listings',
	array(
		'kicker' => __( 'Signature Experiences', 'vm' ),
		'title'  => __( 'The moments they’ll talk about for years', 'vm' ),
		'kind'   => 'experience',
		'count'  => 6,
		'paper'  => true,
	)
);

// 7. Success — reference stats + featured pull-quote.
mcb_part( 'sections/section-stats' );

// 8. Insights.
mcb_part( 'sections/section-news' );

// 9. Partners strip.
mcb_part( 'sections/section-partners' );

// 10. Final CTA.
mcb_part( 'sections/section-cta' );

get_footer();
