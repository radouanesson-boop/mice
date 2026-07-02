<?php
/**
 * Hotel card — venue-card language with hotel meta (star class · rooms).
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_id = (int) ( $args['post_id'] ?? get_the_ID() );

$mcb_stars    = mcb_listing_meta( 'stars', $mcb_id );
$mcb_rooms    = mcb_listing_meta( 'bedrooms', $mcb_id );
$mcb_location = mcb_listing_meta( 'address', $mcb_id );

mcb_part(
	'cards/card-listing',
	array(
		'post_id'  => $mcb_id,
		'kind'     => 'hotel',
		'subtitle' => $mcb_location,
		'meta'     => array(
			array(
				'text' => $mcb_stars ? sprintf( /* translators: %s: hotel star class. */ __( '%s-star', 'vm' ), $mcb_stars ) : '',
			),
			array(
				'text' => $mcb_rooms ? sprintf( /* translators: %s: number of rooms. */ __( '%s rooms', 'vm' ), $mcb_rooms ) : '',
			),
		),
	)
);
