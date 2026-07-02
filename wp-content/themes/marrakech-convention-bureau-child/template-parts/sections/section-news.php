<?php
/**
 * Insights section — board 1A: paper background, eyebrow + title with
 * "Read the journal →" link, three borderless article cards.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'    => __( 'Insights', 'mcb' ),
		'title'     => __( 'Intelligence for event professionals', 'mcb' ),
		'more_text' => __( 'Read the journal', 'mcb' ),
		'more_url'  => get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/insights/' ),
		'count'     => 3,
	)
);

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
<section class="mcb-section mcb-section--paper mcb-section--news">
	<div class="mcb-container">

		<?php
		mcb_part(
			'components/section-heading',
			array(
				'kicker'    => $args['kicker'],
				'title'     => $args['title'],
				'link_text' => $args['more_text'],
				'link_url'  => $args['more_url'],
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
