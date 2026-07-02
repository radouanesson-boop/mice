<?php
/**
 * Generic listings section: heading + dynamic card grid + "view all" link.
 * Used by the PHP homepage; the Elementor equivalent is the combination of
 * the MCB Section Heading + MCB Listings Grid widgets.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'   => '',
		'title'    => '',
		'intro'    => '',
		'kind'     => 'venue',
		'taxonomy' => '',
		'terms'    => array(),
		'count'    => 6,
		'columns'  => 3,
		'more_url' => '',
	)
);

$mcb_query = mcb_listing_query(
	array(
		'count'    => $args['count'],
		'taxonomy' => $args['taxonomy'],
		'terms'    => $args['terms'],
	)
);

if ( ! $mcb_query->have_posts() ) {
	return;
}
?>
<section class="<?php echo mcb_bem( 'mcb-section', array( 'listings', $args['kind'] ) ); ?>">
	<div class="mcb-container">

		<?php
		mcb_part(
			'components/section-heading',
			array(
				'kicker' => $args['kicker'],
				'title'  => $args['title'],
				'intro'  => $args['intro'],
			)
		);
		?>

		<div class="mcb-grid mcb-grid--cols-<?php echo esc_attr( (string) $args['columns'] ); ?>">
			<?php
			while ( $mcb_query->have_posts() ) {
				$mcb_query->the_post();
				mcb_part( 'cards/card-' . sanitize_file_name( $args['kind'] ), array( 'post_id' => get_the_ID() ) );
			}
			wp_reset_postdata();
			?>
		</div>

		<?php if ( $args['more_url'] ) : ?>
			<div class="mcb-section__more">
				<a class="mcb-btn mcb-btn--outline" href="<?php echo esc_url( $args['more_url'] ); ?>">
					<?php esc_html_e( 'View all', 'mcb' ); ?>
					<?php mcb_icon( 'arrow-right' ); ?>
				</a>
			</div>
		<?php endif; ?>

	</div>
</section>
