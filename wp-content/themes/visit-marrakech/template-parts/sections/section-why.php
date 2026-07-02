<?php
/**
 * Why Marrakech — board 1A: heading + lead + outlined chips beside a tall
 * image, followed by the six hairline-top strength stats.
 *
 * All content flows from theme mods and filters; a block-built homepage can
 * rebuild this section from native widgets + the MCB Stats widget.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'   => __( 'Why Marrakech', 'vm' ),
		'title'    => get_theme_mod( 'vm_why_title', __( 'A world-class destination, three hours from Europe', 'vm' ) ),
		'lead'     => get_theme_mod( 'vm_why_lead', __( 'Sun, culture, connectivity and five-star capacity — Marrakech gives your delegates a reason to say yes, and a reason to come back.', 'vm' ) ),
		'image_id' => (int) get_theme_mod( 'vm_why_image', 0 ),
	)
);

$mcb_chips = apply_filters(
	'vm/why_chips',
	array( __( 'Direct flights · 100+ cities°', 'vm' ), __( 'UNESCO Medina', 'vm' ), __( 'Atlas & Agafay', 'vm' ) )
);

$mcb_strengths = apply_filters(
	'vm/strengths',
	array(
		array( 'big' => '3', 'label' => __( 'Dedicated convention centres', 'vm' ), 'note' => __( 'Palais des Congrès, Mogador & Palmeraie', 'vm' ) ),
		array( 'big' => '3h', 'label' => __( 'From major European hubs', 'vm' ), 'note' => __( 'Paris · London · Madrid · Dubai +', 'vm' ) ),
		array( 'big' => '300+', 'label' => __( 'Days of sunshine a year', 'vm' ), 'note' => __( 'An outdoor destination in every season', 'vm' ) ),
		array( 'big' => '1,000s°', 'label' => __( 'Five-star rooms', 'vm' ), 'note' => __( 'Hundreds of meeting spaces citywide', 'vm' ) ),
		array( 'big' => 'UNESCO', 'label' => __( 'World Heritage Medina', 'vm' ), 'note' => __( 'Nine centuries of living culture', 'vm' ) ),
		array( 'big' => '40min', 'label' => __( 'To the Agafay desert', 'vm' ), 'note' => __( 'Atlas peaks & desert camps within reach', 'vm' ) ),
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
