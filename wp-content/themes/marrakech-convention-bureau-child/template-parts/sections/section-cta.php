<?php
/**
 * Closing call-to-action band.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'title'       => get_theme_mod( 'mcb_cta_title', __( 'Ready to bring your event to Marrakech?', 'mcb' ) ),
		'description' => get_theme_mod( 'mcb_cta_description', __( 'Our team offers free, impartial support — from bidding to delegate experiences.', 'mcb' ) ),
		'button_text' => get_theme_mod( 'mcb_cta_button_text', __( 'Talk to the bureau', 'mcb' ) ),
		'button_url'  => get_theme_mod( 'mcb_cta_button_url', home_url( '/contact/' ) ),
	)
);
?>
<section class="mcb-section mcb-section--cta mcb-cta">
	<div class="mcb-container mcb-cta__inner">
		<div class="mcb-cta__content">
			<h2 class="mcb-cta__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php if ( $args['description'] ) : ?>
				<p class="mcb-cta__desc"><?php echo esc_html( $args['description'] ); ?></p>
			<?php endif; ?>
		</div>

		<a class="mcb-btn mcb-btn--light mcb-btn--lg" href="<?php echo esc_url( $args['button_url'] ); ?>">
			<?php echo esc_html( $args['button_text'] ); ?>
			<?php mcb_icon( 'arrow-right' ); ?>
		</a>
	</div>
</section>
