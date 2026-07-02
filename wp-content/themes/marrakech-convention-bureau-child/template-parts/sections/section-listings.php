<?php
/**
 * Generic listings section — board 1A: eyebrow + light title with a
 * "View all →" link on the baseline, dynamic card grid, optional footnote.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'    => '',
		'title'     => '',
		'intro'     => '',
		'kind'      => 'venue',
		'taxonomy'  => '',
		'terms'     => array(),
		'count'     => 3,
		'columns'   => 3,
		'more_text' => '',
		'more_url'  => '',
		'footnote'  => '',
		'paper'     => false,
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
<section class="<?php echo mcb_bem( 'mcb-section', array( 'listings', $args['kind'], $args['paper'] ? 'paper' : null ) ); ?>">
	<div class="mcb-container">

		<?php
		mcb_part(
			'components/section-heading',
			array(
				'kicker'    => $args['kicker'],
				'title'     => $args['title'],
				'intro'     => $args['intro'],
				'link_text' => $args['more_text'],
				'link_url'  => $args['more_url'],
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

		<?php if ( $args['footnote'] ) : ?>
			<p class="mcb-footnote"><?php echo esc_html( $args['footnote'] ); ?></p>
		<?php endif; ?>

	</div>
</section>
