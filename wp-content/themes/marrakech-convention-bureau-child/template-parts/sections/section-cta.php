<?php
/**
 * Final CTA — board 1A: full-bleed image with green scrim, centered
 * eyebrow + light display title + lead + white/outline pill buttons.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'         => get_theme_mod( 'mcb_cta_kicker', __( 'Marrakech Convention Bureau', 'mcb' ) ),
		'title'          => get_theme_mod( 'mcb_cta_title', __( 'Let’s plan something extraordinary', 'mcb' ) ),
		'description'    => get_theme_mod( 'mcb_cta_description', __( 'Tell us about your event. We come back within 48 hours with venues, ideas and a dedicated contact — at no cost.', 'mcb' ) ),
		'primary_text'   => get_theme_mod( 'mcb_cta_button_text', __( 'Submit an RFP', 'mcb' ) ),
		'primary_url'    => get_theme_mod( 'mcb_cta_button_url', home_url( '/submit-rfp/' ) ),
		'secondary_text' => get_theme_mod( 'mcb_cta_button2_text', __( 'Talk to the Bureau', 'mcb' ) ),
		'secondary_url'  => get_theme_mod( 'mcb_cta_button2_url', home_url( '/contact/' ) ),
		'image_id'       => (int) get_theme_mod( 'mcb_cta_image', 0 ),
	)
);
?>
<section class="mcb-cta">
	<div class="mcb-cta__media" aria-hidden="true">
		<?php
		if ( $args['image_id'] ) {
			echo wp_get_attachment_image( $args['image_id'], 'mcb-hero', false, array( 'loading' => 'lazy' ) );
		}
		?>
	</div>

	<div class="mcb-cta__inner">
		<?php if ( $args['kicker'] ) : ?>
			<p class="mcb-eyebrow mcb-eyebrow--on-dark mcb-eyebrow--center"><?php echo esc_html( $args['kicker'] ); ?></p>
		<?php endif; ?>

		<h2 class="mcb-cta__title"><?php echo esc_html( $args['title'] ); ?></h2>

		<?php if ( $args['description'] ) : ?>
			<p class="mcb-cta__desc"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>

		<div class="mcb-cta__actions">
			<?php if ( $args['primary_text'] ) : ?>
				<a class="mcb-btn mcb-btn--light" href="<?php echo esc_url( $args['primary_url'] ); ?>">
					<?php echo esc_html( $args['primary_text'] ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $args['secondary_text'] ) : ?>
				<a class="mcb-btn mcb-btn--outline-light" href="<?php echo esc_url( $args['secondary_url'] ); ?>">
					<?php echo esc_html( $args['secondary_text'] ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
