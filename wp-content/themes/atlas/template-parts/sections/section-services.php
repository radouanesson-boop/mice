<?php
/**
 * Convention Bureau services — board 1A: stone section; left column with
 * heading, chips (Free / Neutral / Confidential) and CTA; right column the
 * numbered service list with Newsreader italic numerals.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'   => __( 'The Convention Bureau', 'dmf' ),
		'title'    => get_theme_mod( 'dmf_cb_title', __( 'One partner, from first bid to closing night', 'dmf' ) ),
		'lead'     => get_theme_mod( 'dmf_cb_lead', __( 'We are your free, neutral and confidential single point of contact — connecting your event to the entire destination.', 'dmf' ) ),
		'cta_text' => __( 'Talk to the Bureau', 'dmf' ),
		'cta_url'  => home_url( '/contact/' ),
		'image_id' => (int) get_theme_mod( 'dmf_cb_image', 0 ),
	)
);

$dmf_chips = apply_filters( 'dmf/bureau_chips', array( __( 'Free', 'dmf' ), __( 'Neutral', 'dmf' ), __( 'Confidential', 'dmf' ) ) );

$dmf_services = apply_filters(
	'dmf/services',
	array(
		array( 'name' => __( 'Venue sourcing', 'dmf' ), 'desc' => __( 'Curated shortlists matched to your programme, capacity and budget.', 'dmf' ) ),
		array( 'name' => __( 'Bid support', 'dmf' ), 'desc' => __( 'Data, support letters and lobbying to help you win the bid.', 'dmf' ) ),
		array( 'name' => __( 'Site inspections', 'dmf' ), 'desc' => __( 'We host and organise your familiarisation and inspection visits.', 'dmf' ) ),
		array( 'name' => __( 'Supplier network', 'dmf' ), 'desc' => __( 'Vetted DMCs, agencies, caterers, production and transport.', 'dmf' ) ),
		array( 'name' => __( 'DMC connections', 'dmf' ), 'desc' => __( 'Warm introductions to Marrakech’s most trusted operators.', 'dmf' ) ),
		array( 'name' => __( 'Planning assistance', 'dmf' ), 'desc' => __( 'A single dedicated contact from first enquiry to closing night.', 'dmf' ) ),
	)
);

if ( empty( $dmf_services ) ) {
	return;
}
?>
<section class="dmf-section dmf-section--stone dmf-bureau">
	<div class="dmf-container dmf-bureau__grid">

		<div class="dmf-bureau__aside">
			<p class="dmf-eyebrow"><?php echo esc_html( $args['kicker'] ); ?></p>
			<h2 class="dmf-section-heading__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<p class="dmf-section-heading__intro"><?php echo esc_html( $args['lead'] ); ?></p>

			<?php if ( ! empty( $dmf_chips ) ) : ?>
				<div class="dmf-bureau__chips">
					<?php foreach ( $dmf_chips as $dmf_chip ) : ?>
						<span class="dmf-badge dmf-badge--chip"><?php echo esc_html( $dmf_chip ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $args['cta_text'] ) : ?>
				<a class="dmf-btn dmf-btn--primary dmf-bureau__cta" href="<?php echo esc_url( $args['cta_url'] ); ?>">
					<?php echo esc_html( $args['cta_text'] ); ?>
					<?php dmf_icon( 'arrow-right' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $args['image_id'] ) : ?>
				<div class="dmf-bureau__media">
					<?php echo wp_get_attachment_image( $args['image_id'], 'dmf-card-wide', false, array( 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="dmf-services">
			<?php foreach ( array_values( $dmf_services ) as $dmf_i => $dmf_service ) : ?>
				<div class="dmf-services__item">
					<div class="dmf-services__num"><?php echo esc_html( str_pad( (string) ( $dmf_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></div>
					<div>
						<h3 class="dmf-services__name"><?php echo esc_html( $dmf_service['name'] ); ?></h3>
						<?php if ( ! empty( $dmf_service['desc'] ) ) : ?>
							<p class="dmf-services__desc"><?php echo esc_html( $dmf_service['desc'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
