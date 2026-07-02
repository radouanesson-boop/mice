<?php
/**
 * Insights section — board 1A: paper background, eyebrow + title with
 * "Read the journal →" link, three borderless article cards.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'    => __( 'Insights', 'dmf' ),
		'title'     => __( 'Intelligence for event professionals', 'dmf' ),
		'more_text' => __( 'Read the journal', 'dmf' ),
		'more_url'  => get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/insights/' ),
		'count'     => 3,
	)
);

$dmf_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => (int) $args['count'],
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $dmf_query->have_posts() ) {
	return;
}
?>
<section class="dmf-section dmf-section--paper dmf-section--news">
	<div class="dmf-container">

		<?php
		dmf_part(
			'components/section-heading',
			array(
				'kicker'    => $args['kicker'],
				'title'     => $args['title'],
				'link_text' => $args['more_text'],
				'link_url'  => $args['more_url'],
			)
		);
		?>

		<div class="dmf-grid dmf-grid--cols-3">
			<?php
			while ( $dmf_query->have_posts() ) {
				$dmf_query->the_post();
				dmf_part( 'cards/card-news', array( 'post_id' => get_the_ID() ) );
			}
			wp_reset_postdata();
			?>
		</div>

	</div>
</section>
