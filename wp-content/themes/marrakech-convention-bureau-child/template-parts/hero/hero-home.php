<?php
/**
 * Homepage hero — full-bleed image, gradient overlay, kicker/title/copy,
 * dual CTAs and the integrated listing search bar.
 *
 * Data comes from $args (Elementor widget or page template). Every field
 * has an editor-facing default and can be overridden without code.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'          => get_theme_mod( 'mcb_hero_kicker', __( 'Marrakech Convention Bureau', 'mcb' ) ),
		'title'           => get_theme_mod( 'mcb_hero_title', __( 'Where world-class events meet timeless wonder', 'mcb' ) ),
		'description'     => get_theme_mod( 'mcb_hero_description', __( 'Your trusted partner for meetings, incentives, conferences and exhibitions in the Red City.', 'mcb' ) ),
		'primary_text'    => __( 'Plan your event', 'mcb' ),
		'primary_url'     => home_url( '/plan-your-event/' ),
		'secondary_text'  => __( 'Explore venues', 'mcb' ),
		'secondary_url'   => home_url( '/venues/' ),
		'show_search'     => true,
		'image_url'       => '',
		'image_id'        => (int) get_theme_mod( 'mcb_hero_image', 0 ),
		'overlay_opacity' => 55,
	)
);

$mcb_is_h1 = is_front_page();
?>
<section class="mcb-hero" style="--mcb-hero-overlay: <?php echo esc_attr( $args['overlay_opacity'] / 100 ); ?>;">

	<div class="mcb-hero__media" aria-hidden="true">
		<?php
		if ( $args['image_id'] ) {
			echo wp_get_attachment_image(
				$args['image_id'],
				'mcb-hero',
				false,
				array(
					'class'         => 'mcb-hero__img',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
				)
			);
		} elseif ( $args['image_url'] ) {
			printf(
				'<img class="mcb-hero__img" src="%s" alt="" loading="eager" fetchpriority="high">',
				esc_url( $args['image_url'] )
			);
		}
		?>
		<span class="mcb-hero__overlay"></span>
	</div>

	<div class="mcb-container mcb-hero__inner">
		<?php if ( $args['kicker'] ) : ?>
			<p class="mcb-hero__kicker"><?php echo esc_html( $args['kicker'] ); ?></p>
		<?php endif; ?>

		<?php printf( '<%1$s class="mcb-hero__title">%2$s</%1$s>', $mcb_is_h1 ? 'h1' : 'h2', esc_html( $args['title'] ) ); ?>

		<?php if ( $args['description'] ) : ?>
			<p class="mcb-hero__desc"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>

		<div class="mcb-hero__actions">
			<?php if ( $args['primary_text'] ) : ?>
				<a class="mcb-btn mcb-btn--primary mcb-btn--lg" href="<?php echo esc_url( $args['primary_url'] ); ?>">
					<?php echo esc_html( $args['primary_text'] ); ?>
					<?php mcb_icon( 'arrow-right' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $args['secondary_text'] ) : ?>
				<a class="mcb-btn mcb-btn--ghost mcb-btn--lg" href="<?php echo esc_url( $args['secondary_url'] ); ?>">
					<?php echo esc_html( $args['secondary_text'] ); ?>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( $args['show_search'] ) : ?>
			<?php mcb_part( 'components/search-bar' ); ?>
		<?php endif; ?>
	</div>

</section>
