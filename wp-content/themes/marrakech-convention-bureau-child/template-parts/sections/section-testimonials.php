<?php
/**
 * Testimonials — pulls a "testimonial" post type when one exists (Brikk
 * installs often register one), otherwise renders nothing rather than
 * hardcoding content. In Elementor, use its native testimonial/loop
 * widgets styled by .mcb-testimonial classes.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_post_type = apply_filters( 'mcb/testimonial_post_type', 'testimonial' );

if ( ! post_type_exists( $mcb_post_type ) ) {
	return;
}

$mcb_query = new WP_Query(
	array(
		'post_type'      => $mcb_post_type,
		'posts_per_page' => 3,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	)
);

if ( ! $mcb_query->have_posts() ) {
	return;
}
?>
<section class="mcb-section mcb-section--testimonials">
	<div class="mcb-container">

		<?php
		mcb_part(
			'components/section-heading',
			array(
				'kicker' => __( 'Testimonials', 'mcb' ),
				'title'  => __( 'Planners on Marrakech', 'mcb' ),
			)
		);
		?>

		<div class="mcb-grid mcb-grid--cols-3">
			<?php
			while ( $mcb_query->have_posts() ) :
				$mcb_query->the_post();
				?>
				<figure class="mcb-testimonial">
					<blockquote class="mcb-testimonial__quote">
						<?php mcb_icon( 'quote' ); ?>
						<?php the_content(); ?>
					</blockquote>
					<figcaption class="mcb-testimonial__author">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'thumbnail', array( 'class' => 'mcb-testimonial__avatar', 'loading' => 'lazy' ) ); ?>
						<?php endif; ?>
						<div>
							<span class="mcb-testimonial__name"><?php the_title(); ?></span>
							<?php if ( has_excerpt() ) : ?>
								<span class="mcb-testimonial__role"><?php echo esc_html( get_the_excerpt() ); ?></span>
							<?php endif; ?>
						</div>
					</figcaption>
				</figure>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>

	</div>
</section>
