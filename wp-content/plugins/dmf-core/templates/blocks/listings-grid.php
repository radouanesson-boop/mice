<?php
/**
 * Block: dmf/listings-grid — dynamic listing grid.
 *
 * @var array $args { type, count, columns, taxonomy, terms }
 * @package DMF
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? array(), array( 'type' => '', 'count' => 6, 'columns' => 3, 'taxonomy' => '', 'terms' => '' ) );

$dmf_query = dmf_listing_query(
	array(
		'type'     => $args['type'] ? array_map( 'sanitize_key', explode( ',', (string) $args['type'] ) ) : array(),
		'count'    => (int) $args['count'],
		'taxonomy' => sanitize_key( (string) $args['taxonomy'] ),
		'terms'    => array_filter( array_map( 'sanitize_title', explode( ',', (string) $args['terms'] ) ) ),
	)
);

if ( ! $dmf_query->have_posts() ) {
	return;
}
?>
<div class="dmf-grid dmf-grid--cols-<?php echo esc_attr( (string) (int) $args['columns'] ); ?>">
	<?php
	while ( $dmf_query->have_posts() ) {
		$dmf_query->the_post();
		dmf_template( 'cards/listing', array( 'post_id' => get_the_ID() ) );
	}
	wp_reset_postdata();
	?>
</div>
