<?php
/**
 * Generic listings section — board 1A: eyebrow + light title with a
 * "View all →" link on the baseline, dynamic card grid, optional footnote.
 *
 * @package Atlas
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

$dmf_query = dmf_listing_query(
	array(
		'count'    => $args['count'],
		'taxonomy' => $args['taxonomy'],
		'terms'    => $args['terms'],
	)
);

if ( ! $dmf_query->have_posts() ) {
	return;
}
?>
<section class="<?php echo dmf_bem( 'dmf-section', array( 'listings', $args['kind'], $args['paper'] ? 'paper' : null ) ); ?>">
	<div class="dmf-container">

		<?php
		dmf_part(
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

		<div class="dmf-grid dmf-grid--cols-<?php echo esc_attr( (string) $args['columns'] ); ?>">
			<?php
			while ( $dmf_query->have_posts() ) {
				$dmf_query->the_post();
				dmf_part( 'cards/card-' . sanitize_file_name( $args['kind'] ), array( 'post_id' => get_the_ID() ) );
			}
			wp_reset_postdata();
			?>
		</div>

		<?php if ( $args['footnote'] ) : ?>
			<p class="dmf-footnote"><?php echo esc_html( $args['footnote'] ); ?></p>
		<?php endif; ?>

	</div>
</section>
