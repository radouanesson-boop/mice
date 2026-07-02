<?php
/**
 * Hotel card — venue-card language with hotel meta (star class · rooms).
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$dmf_id = (int) ( $args['post_id'] ?? get_the_ID() );

$dmf_stars    = dmf_listing_meta( 'stars', $dmf_id );
$dmf_rooms    = dmf_listing_meta( 'rooms', $dmf_id );
$dmf_location = dmf_listing_meta( 'location', $dmf_id );

dmf_part(
	'cards/card-listing',
	array(
		'post_id'  => $dmf_id,
		'kind'     => 'hotel',
		'subtitle' => $dmf_location,
		'meta'     => array(
			array(
				'text' => $dmf_stars ? sprintf( /* translators: %s: hotel star class. */ __( '%s-star', 'dmf' ), $dmf_stars ) : '',
			),
			array(
				'text' => $dmf_rooms ? sprintf( /* translators: %s: number of rooms. */ __( '%s rooms', 'dmf' ), $dmf_rooms ) : '',
			),
		),
	)
);
