<?php
/**
 * Hotel card — star class, rooms, meeting space.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_id = (int) ( $args['post_id'] ?? get_the_ID() );

$mcb_stars    = mcb_listing_meta( 'stars', $mcb_id );
$mcb_rooms    = mcb_listing_meta( 'rooms', $mcb_id );
$mcb_location = mcb_listing_meta( 'location', $mcb_id );

mcb_part(
	'cards/card-listing',
	array(
		'post_id' => $mcb_id,
		'kind'    => 'hotel',
		'meta'    => array(
			array(
				'icon' => 'star',
				'text' => $mcb_stars ? sprintf( /* translators: %s: hotel star class. */ __( '%s-star hotel', 'mcb' ), $mcb_stars ) : '',
			),
			array(
				'icon' => 'bed',
				'text' => $mcb_rooms ? sprintf( /* translators: %s: number of rooms. */ __( '%s rooms', 'mcb' ), number_format_i18n( (float) $mcb_rooms ) ) : '',
			),
			array(
				'icon' => 'map-pin',
				'text' => $mcb_location,
			),
		),
	)
);
