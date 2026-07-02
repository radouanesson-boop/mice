<?php
/**
 * Event card — date block, venue and attendance.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_id = (int) ( $args['post_id'] ?? get_the_ID() );

$mcb_date  = mcb_listing_meta( 'start_date', $mcb_id );
$mcb_venue = mcb_listing_meta( 'event_venue', $mcb_id );

$mcb_footer = '';
if ( $mcb_date ) {
	$mcb_timestamp = is_numeric( $mcb_date ) ? (int) $mcb_date : strtotime( (string) $mcb_date );

	if ( $mcb_timestamp ) {
		$mcb_footer = sprintf(
			'<time class="mcb-card__date" datetime="%s"><span class="mcb-card__date-day">%s</span><span class="mcb-card__date-month">%s</span></time>',
			esc_attr( gmdate( 'Y-m-d', $mcb_timestamp ) ),
			esc_html( date_i18n( 'j', $mcb_timestamp ) ),
			esc_html( date_i18n( 'M', $mcb_timestamp ) )
		);
	}
}

mcb_part(
	'cards/card-listing',
	array(
		'post_id' => $mcb_id,
		'kind'    => 'event',
		'size'    => 'mcb-card-wide',
		'meta'    => array(
			array(
				'icon' => 'map-pin',
				'text' => $mcb_venue,
			),
		),
		'footer'  => $mcb_footer,
	)
);
