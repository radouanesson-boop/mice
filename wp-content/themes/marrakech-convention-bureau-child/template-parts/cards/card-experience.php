<?php
/**
 * Experience card — tall imagery, duration and group size.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_id = (int) ( $args['post_id'] ?? get_the_ID() );

$mcb_duration = mcb_listing_meta( 'duration', $mcb_id );
$mcb_group    = mcb_listing_meta( 'group_size', $mcb_id );

mcb_part(
	'cards/card-listing',
	array(
		'post_id' => $mcb_id,
		'kind'    => 'experience',
		'size'    => 'mcb-card-tall',
		'meta'    => array(
			array(
				'icon' => 'clock',
				'text' => $mcb_duration,
			),
			array(
				'icon' => 'users',
				'text' => $mcb_group ? sprintf( /* translators: %s: group size. */ __( 'Groups up to %s', 'mcb' ), number_format_i18n( (float) $mcb_group ) ) : '',
			),
		),
	)
);
