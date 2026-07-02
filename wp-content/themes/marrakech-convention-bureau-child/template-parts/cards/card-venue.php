<?php
/**
 * Venue card — board 1A: tag pill, name, sage operator sub-line, hairline
 * meta row (capacity · rooms).
 *
 * Meta keys are logical; map them to your Routiz field keys once via the
 * `mcb/listing_meta_map` filter (see docs/CONTENT-MAPPING.md).
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_id = (int) ( $args['post_id'] ?? get_the_ID() );

$mcb_capacity = mcb_listing_meta( 'capacity', $mcb_id );
$mcb_rooms    = mcb_listing_meta( 'rooms', $mcb_id );
$mcb_operator = mcb_listing_meta( 'operator', $mcb_id );

mcb_part(
	'cards/card-listing',
	array(
		'post_id'  => $mcb_id,
		'kind'     => 'venue',
		'subtitle' => $mcb_operator ? $mcb_operator : mcb_listing_meta( 'location', $mcb_id ),
		'meta'     => array(
			array(
				'text' => $mcb_capacity ? sprintf( /* translators: %s: number of delegates. */ __( 'Up to %s pax', 'mcb' ), $mcb_capacity ) : '',
			),
			array(
				'text' => $mcb_rooms ? sprintf( /* translators: %s: breakout rooms. */ __( '%s breakout rooms', 'mcb' ), $mcb_rooms ) : '',
			),
		),
	)
);
