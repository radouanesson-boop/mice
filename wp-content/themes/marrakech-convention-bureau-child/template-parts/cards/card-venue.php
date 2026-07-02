<?php
/**
 * Venue card — capacity, location, venue type.
 *
 * Meta keys are logical; map them to your Routiz field keys once via the
 * `mcb/listing_meta_map` filter (see docs/CONTENT-MAPPING.md).
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_id = (int) ( $args['post_id'] ?? get_the_ID() );

$mcb_capacity = mcb_listing_meta( 'capacity', $mcb_id );
$mcb_location = mcb_listing_meta( 'location', $mcb_id );

mcb_part(
	'cards/card-listing',
	array(
		'post_id' => $mcb_id,
		'kind'    => 'venue',
		'meta'    => array(
			array(
				'icon' => 'users',
				'text' => $mcb_capacity ? sprintf( /* translators: %s: number of guests. */ __( 'Up to %s delegates', 'mcb' ), number_format_i18n( (float) $mcb_capacity ) ) : '',
			),
			array(
				'icon' => 'map-pin',
				'text' => $mcb_location,
			),
		),
	)
);
