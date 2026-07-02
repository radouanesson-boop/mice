<?php
/**
 * Convention Bureau services — board 1A: stone section; left column with
 * heading, chips (Free / Neutral / Confidential) and CTA; right column the
 * numbered service list with Newsreader italic numerals.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'   => __( 'The Convention Bureau', 'vm' ),
		'title'    => get_theme_mod( 'vm_cb_title', __( 'One partner, from first bid to closing night', 'vm' ) ),
		'lead'     => get_theme_mod( 'vm_cb_lead', __( 'We are your free, neutral and confidential single point of contact — connecting your event to the entire destination.', 'vm' ) ),
		'cta_text' => __( 'Talk to the Bureau', 'vm' ),
		'cta_url'  => home_url( '/contact/' ),
		'image_id' => (int) get_theme_mod( 'vm_cb_image', 0 ),
	)
);

$mcb_chips = apply_filters( 'vm/bureau_chips', array( __( 'Free', 'vm' ), __( 'Neutral', 'vm' ), __( 'Confidential', 'vm' ) ) );

$mcb_services = apply_filters(
	'vm/services',
	array(
		array( 'name' => __( 'Venue sourcing', 'vm' ), 'desc' => __( 'Curated shortlists matched to your programme, capacity and budget.', 'vm' ) ),
		array( 'name' => __( 'Bid support', 'vm' ), 'desc' => __( 'Data, support letters and lobbying to help you win the bid.', 'vm' ) ),
		array( 'name' => __( 'Site inspections', 'vm' ), 'desc' => __( 'We host and organise your familiarisation and inspection visits.', 'vm' ) ),
		array( 'name' => __( 'Supplier network', 'vm' ), 'desc' => __( 'Vetted DMCs, agencies, caterers, production and transport.', 'vm' ) ),
		array( 'name' => __( 'DMC connections', 'vm' ), 'desc' => __( 'Warm introductions to Marrakech’s most trusted operators.', 'vm' ) ),
		array( 'name' => __( 'Planning assistance', 'vm' ), 'desc' => __( 'A single dedicated contact from first enquiry to closing night.', 'vm' ) ),
	)
);

if ( empty( $mcb_services ) ) {
	return;
}
?>
<section class="mcb-section mcb-section--stone mcb-bureau">
	<div class="mcb-container mcb-bureau__grid">

		<div class="mcb-bureau__aside">
			<p class="mcb-eyebrow"><?php echo esc_html( $args['kicker'] ); ?></p>
			<h2 class="mcb-section-heading__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<p class="mcb-section-heading__intro"><?php echo esc_html( $args['lead'] ); ?></p>

			<?php if ( ! empty( $mcb_chips ) ) : ?>
				<div class="mcb-bureau__chips">
					<?php foreach ( $mcb_chips as $mcb_chip ) : ?>
						<span class="mcb-badge mcb-badge--chip"><?php echo esc_html( $mcb_chip ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $args['cta_text'] ) : ?>
				<a class="mcb-btn mcb-btn--primary mcb-bureau__cta" href="<?php echo esc_url( $args['cta_url'] ); ?>">
					<?php echo esc_html( $args['cta_text'] ); ?>
					<?php mcb_icon( 'arrow-right' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $args['image_id'] ) : ?>
				<div class="mcb-bureau__media">
					<?php echo wp_get_attachment_image( $args['image_id'], 'mcb-card-wide', false, array( 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="mcb-services">
			<?php foreach ( array_values( $mcb_services ) as $mcb_i => $mcb_service ) : ?>
				<div class="mcb-services__item">
					<div class="mcb-services__num"><?php echo esc_html( str_pad( (string) ( $mcb_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></div>
					<div>
						<h3 class="mcb-services__name"><?php echo esc_html( $mcb_service['name'] ); ?></h3>
						<?php if ( ! empty( $mcb_service['desc'] ) ) : ?>
							<p class="mcb-services__desc"><?php echo esc_html( $mcb_service['desc'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
