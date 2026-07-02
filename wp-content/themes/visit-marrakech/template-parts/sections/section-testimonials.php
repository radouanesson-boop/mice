<?php
/**
 * Featured pull-quote — board 1A: one large centered Newsreader italic
 * quote with attribution. Pulls the most recent "testimonial" post when
 * that post type exists; renders nothing otherwise (no hardcoded content).
 *
 * Content model: quote = post content, name = post title, role = excerpt.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_post_type = apply_filters( 'vm/testimonial_post_type', 'testimonial' );

if ( ! post_type_exists( $mcb_post_type ) ) {
	return;
}

$mcb_query = new WP_Query(
	array(
		'post_type'      => $mcb_post_type,
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	)
);

if ( ! $mcb_query->have_posts() ) {
	return;
}

while ( $mcb_query->have_posts() ) :
	$mcb_query->the_post();
	?>
	<figure class="mcb-quote">
		<blockquote class="mcb-quote__text">
			<?php echo esc_html( wp_strip_all_tags( get_the_content() ) ); ?>
		</blockquote>
		<figcaption>
			<div class="mcb-quote__name"><?php the_title(); ?></div>
			<?php if ( has_excerpt() ) : ?>
				<div class="mcb-quote__role"><?php echo esc_html( get_the_excerpt() ); ?></div>
			<?php endif; ?>
		</figcaption>
	</figure>
	<?php
endwhile;

wp_reset_postdata();
