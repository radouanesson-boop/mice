<?php
/**
 * Event card — date block, venue and attendance.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$dmf_id = (int) ( $args['post_id'] ?? get_the_ID() );

$dmf_date  = dmf_listing_meta( 'event_date', $dmf_id );
$dmf_venue = dmf_listing_meta( 'event_venue', $dmf_id );

$dmf_footer = '';
if ( $dmf_date ) {
	$dmf_timestamp = is_numeric( $dmf_date ) ? (int) $dmf_date : strtotime( (string) $dmf_date );

	if ( $dmf_timestamp ) {
		$dmf_footer = sprintf(
			'<time class="dmf-card__date" datetime="%s"><span class="dmf-card__date-day">%s</span><span class="dmf-card__date-month">%s</span></time>',
			esc_attr( gmdate( 'Y-m-d', $dmf_timestamp ) ),
			esc_html( date_i18n( 'j', $dmf_timestamp ) ),
			esc_html( date_i18n( 'M', $dmf_timestamp ) )
		);
	}
}

dmf_part(
	'cards/card-listing',
	array(
		'post_id' => $dmf_id,
		'kind'    => 'event',
		'size'    => 'dmf-card-wide',
		'meta'    => array(
			array(
				'icon' => 'map-pin',
				'text' => $dmf_venue,
			),
		),
		'footer'  => $dmf_footer,
	)
);
