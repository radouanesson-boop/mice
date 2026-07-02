<?php
/**
 * Latest news section (standard posts).
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? array(), array( 'count' => 3 ) );

$mcb_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => (int) $args['count'],
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $mcb_query->have_posts() ) {
	return;
}
?>
<section class="mcb-section mcb-section--news">
	<div class="mcb-container">

		<?php
		mcb_part(
			'components/section-heading',
			array(
				'kicker' => __( 'News', 'mcb' ),
				'title'  => __( 'Latest from the bureau', 'mcb' ),
			)
		);
		?>

		<div class="mcb-grid mcb-grid--cols-3">
			<?php
			while ( $mcb_query->have_posts() ) {
				$mcb_query->the_post();
				mcb_part( 'cards/card-news', array( 'post_id' => get_the_ID() ) );
			}
			wp_reset_postdata();
			?>
		</div>

	</div>
</section>
