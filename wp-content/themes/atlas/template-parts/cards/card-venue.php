<?php
/**
 * Venue card — board 1A: tag pill, name, sage operator sub-line, hairline
 * meta row (capacity · rooms).
 *
 * Meta keys are logical; map them to your Routiz field keys once via the
 * `dmf/listing_meta_map` filter (see docs/CONTENT-MAPPING.md).
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$dmf_id = (int) ( $args['post_id'] ?? get_the_ID() );

$dmf_capacity = dmf_listing_meta( 'capacity', $dmf_id );
$dmf_rooms    = dmf_listing_meta( 'rooms', $dmf_id );
$dmf_operator = dmf_listing_meta( 'operator', $dmf_id );

dmf_part(
	'cards/card-listing',
	array(
		'post_id'  => $dmf_id,
		'kind'     => 'venue',
		'subtitle' => $dmf_operator ? $dmf_operator : dmf_listing_meta( 'location', $dmf_id ),
		'meta'     => array(
			array(
				'text' => $dmf_capacity ? sprintf( /* translators: %s: number of delegates. */ __( 'Up to %s pax', 'dmf' ), $dmf_capacity ) : '',
			),
			array(
				'text' => $dmf_rooms ? sprintf( /* translators: %s: breakout rooms. */ __( '%s breakout rooms', 'dmf' ), $dmf_rooms ) : '',
			),
		),
	)
);
