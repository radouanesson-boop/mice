<?php
/**
 * Why Marrakech — board 1A: heading + lead + outlined chips beside a tall
 * image, followed by the six hairline-top strength stats.
 *
 * All content flows from theme mods and filters; the Elementor homepage can
 * rebuild this section from native widgets + the DMF Stats block.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'   => __( 'Why Marrakech', 'dmf' ),
		'title'    => get_theme_mod( 'dmf_why_title', __( 'A world-class destination, three hours from Europe', 'dmf' ) ),
		'lead'     => get_theme_mod( 'dmf_why_lead', __( 'Sun, culture, connectivity and five-star capacity — Marrakech gives your delegates a reason to say yes, and a reason to come back.', 'dmf' ) ),
		'image_id' => (int) get_theme_mod( 'dmf_why_image', 0 ),
	)
);

$dmf_chips = apply_filters(
	'dmf/why_chips',
	array( __( 'Direct flights · 100+ cities°', 'dmf' ), __( 'UNESCO Medina', 'dmf' ), __( 'Atlas & Agafay', 'dmf' ) )
);

$dmf_strengths = apply_filters(
	'dmf/strengths',
	array(
		array( 'big' => '3', 'label' => __( 'Dedicated convention centres', 'dmf' ), 'note' => __( 'Palais des Congrès, Mogador & Palmeraie', 'dmf' ) ),
		array( 'big' => '3h', 'label' => __( 'From major European hubs', 'dmf' ), 'note' => __( 'Paris · London · Madrid · Dubai +', 'dmf' ) ),
		array( 'big' => '300+', 'label' => __( 'Days of sunshine a year', 'dmf' ), 'note' => __( 'An outdoor destination in every season', 'dmf' ) ),
		array( 'big' => '1,000s°', 'label' => __( 'Five-star rooms', 'dmf' ), 'note' => __( 'Hundreds of meeting spaces citywide', 'dmf' ) ),
		array( 'big' => 'UNESCO', 'label' => __( 'World Heritage Medina', 'dmf' ), 'note' => __( 'Nine centuries of living culture', 'dmf' ) ),
		array( 'big' => '40min', 'label' => __( 'To the Agafay desert', 'dmf' ), 'note' => __( 'Atlas peaks & desert camps within reach', 'dmf' ) ),
	)
);
?>
<section class="dmf-section dmf-section--why">
	<div class="dmf-container">

		<div class="dmf-why">
			<div class="dmf-why__content">
				<p class="dmf-eyebrow"><?php echo esc_html( $args['kicker'] ); ?></p>
				<h2 class="dmf-section-heading__title"><?php echo esc_html( $args['title'] ); ?></h2>
				<p class="dmf-section-heading__intro"><?php echo esc_html( $args['lead'] ); ?></p>

				<?php if ( ! empty( $dmf_chips ) ) : ?>
					<div class="dmf-why__chips">
						<?php foreach ( $dmf_chips as $dmf_chip ) : ?>
							<span class="dmf-badge dmf-badge--outline"><?php echo esc_html( $dmf_chip ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $args['image_id'] ) : ?>
				<div class="dmf-why__media">
					<?php echo wp_get_attachment_image( $args['image_id'], 'dmf-card-wide', false, array( 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $dmf_strengths ) ) : ?>
			<div class="dmf-strengths">
				<?php foreach ( $dmf_strengths as $dmf_s ) : ?>
					<div class="dmf-strengths__item">
						<div class="dmf-strengths__big"><?php echo esc_html( $dmf_s['big'] ); ?></div>
						<div class="dmf-strengths__label"><?php echo esc_html( $dmf_s['label'] ); ?></div>
						<?php if ( ! empty( $dmf_s['note'] ) ) : ?>
							<div class="dmf-strengths__note"><?php echo esc_html( $dmf_s['note'] ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
