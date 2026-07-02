<?php
/**
 * Featured pull-quote — board 1A: one large centered Newsreader italic
 * quote with attribution. Pulls the most recent "testimonial" post when
 * that post type exists; renders nothing otherwise (no hardcoded content).
 *
 * Content model: quote = post content, name = post title, role = excerpt.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$dmf_post_type = apply_filters( 'dmf/testimonial_post_type', 'testimonial' );

if ( ! post_type_exists( $dmf_post_type ) ) {
	return;
}

$dmf_query = new WP_Query(
	array(
		'post_type'      => $dmf_post_type,
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	)
);

if ( ! $dmf_query->have_posts() ) {
	return;
}

while ( $dmf_query->have_posts() ) :
	$dmf_query->the_post();
	?>
	<figure class="dmf-quote">
		<blockquote class="dmf-quote__text">
			<?php echo esc_html( wp_strip_all_tags( get_the_content() ) ); ?>
		</blockquote>
		<figcaption>
			<div class="dmf-quote__name"><?php the_title(); ?></div>
			<?php if ( has_excerpt() ) : ?>
				<div class="dmf-quote__role"><?php echo esc_html( get_the_excerpt() ); ?></div>
			<?php endif; ?>
		</figcaption>
	</figure>
	<?php
endwhile;

wp_reset_postdata();
