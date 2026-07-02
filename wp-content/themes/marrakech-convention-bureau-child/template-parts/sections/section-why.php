<?php
/**
 * Why Marrakech — board 1A: heading + lead + outlined chips beside a tall
 * image, followed by the six hairline-top strength stats.
 *
 * All content flows from theme mods and filters; the Elementor homepage can
 * rebuild this section from native widgets + the MCB Stats widget.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'   => __( 'Why Marrakech', 'mcb' ),
		'title'    => get_theme_mod( 'mcb_why_title', __( 'A world-class destination, three hours from Europe', 'mcb' ) ),
		'lead'     => get_theme_mod( 'mcb_why_lead', __( 'Sun, culture, connectivity and five-star capacity — Marrakech gives your delegates a reason to say yes, and a reason to come back.', 'mcb' ) ),
		'image_id' => (int) get_theme_mod( 'mcb_why_image', 0 ),
	)
);

$mcb_chips = apply_filters(
	'mcb/why_chips',
	array( __( 'Direct flights · 100+ cities°', 'mcb' ), __( 'UNESCO Medina', 'mcb' ), __( 'Atlas & Agafay', 'mcb' ) )
);

$mcb_strengths = apply_filters(
	'mcb/strengths',
	array(
		array( 'big' => '3', 'label' => __( 'Dedicated convention centres', 'mcb' ), 'note' => __( 'Palais des Congrès, Mogador & Palmeraie', 'mcb' ) ),
		array( 'big' => '3h', 'label' => __( 'From major European hubs', 'mcb' ), 'note' => __( 'Paris · London · Madrid · Dubai +', 'mcb' ) ),
		array( 'big' => '300+', 'label' => __( 'Days of sunshine a year', 'mcb' ), 'note' => __( 'An outdoor destination in every season', 'mcb' ) ),
		array( 'big' => '1,000s°', 'label' => __( 'Five-star rooms', 'mcb' ), 'note' => __( 'Hundreds of meeting spaces citywide', 'mcb' ) ),
		array( 'big' => 'UNESCO', 'label' => __( 'World Heritage Medina', 'mcb' ), 'note' => __( 'Nine centuries of living culture', 'mcb' ) ),
		array( 'big' => '40min', 'label' => __( 'To the Agafay desert', 'mcb' ), 'note' => __( 'Atlas peaks & desert camps within reach', 'mcb' ) ),
	)
);
?>
<section class="mcb-section mcb-section--why">
	<div class="mcb-container">

		<div class="mcb-why">
			<div class="mcb-why__content">
				<p class="mcb-eyebrow"><?php echo esc_html( $args['kicker'] ); ?></p>
				<h2 class="mcb-section-heading__title"><?php echo esc_html( $args['title'] ); ?></h2>
				<p class="mcb-section-heading__intro"><?php echo esc_html( $args['lead'] ); ?></p>

				<?php if ( ! empty( $mcb_chips ) ) : ?>
					<div class="mcb-why__chips">
						<?php foreach ( $mcb_chips as $mcb_chip ) : ?>
							<span class="mcb-badge mcb-badge--outline"><?php echo esc_html( $mcb_chip ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $args['image_id'] ) : ?>
				<div class="mcb-why__media">
					<?php echo wp_get_attachment_image( $args['image_id'], 'mcb-card-wide', false, array( 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $mcb_strengths ) ) : ?>
			<div class="mcb-strengths">
				<?php foreach ( $mcb_strengths as $mcb_s ) : ?>
					<div class="mcb-strengths__item">
						<div class="mcb-strengths__big"><?php echo esc_html( $mcb_s['big'] ); ?></div>
						<div class="mcb-strengths__label"><?php echo esc_html( $mcb_s['label'] ); ?></div>
						<?php if ( ! empty( $mcb_s['note'] ) ) : ?>
							<div class="mcb-strengths__note"><?php echo esc_html( $mcb_s['note'] ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
