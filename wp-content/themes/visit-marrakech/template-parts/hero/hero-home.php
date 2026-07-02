<?php
/**
 * Homepage hero — design board 1A: split layout. Left: eyebrow, light
 * display title with an italic Newsreader accent word, lead, two pill CTAs.
 * Right: cinematic image with soft gradient and a frosted stats card.
 * Mobile (board 1C): image on top with the title over it, stacked buttons,
 * 3-up stat row.
 *
 * Every field comes from $args (front-page or block context) with
 * theme-mod defaults — nothing hardcoded.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'          => get_theme_mod( 'vm_hero_kicker', __( 'Marrakech Convention Bureau', 'vm' ) ),
		// Title in three parts so the accent word renders in Newsreader italic.
		'title_before'    => get_theme_mod( 'vm_hero_title_before', __( 'Where the world meets ', 'vm' ) ),
		'title_em'        => get_theme_mod( 'vm_hero_title_em', __( 'Marrakech', 'vm' ) ),
		'title_after'     => get_theme_mod( 'vm_hero_title_after', '' ),
		'description'     => get_theme_mod( 'vm_hero_description', __( 'Africa’s rising capital for meetings, incentives, congresses and events — supported end to end by the Marrakech Convention Bureau.', 'vm' ) ),
		'primary_text'    => __( 'Submit an RFP', 'vm' ),
		'primary_url'     => home_url( '/submit-rfp/' ),
		'secondary_text'  => __( 'Explore the destination', 'vm' ),
		'secondary_url'   => home_url( '/destination/' ),
		'show_search'     => false,
		'show_stats'      => true,
		'image_url'       => '',
		'image_id'        => (int) get_theme_mod( 'vm_hero_image', 0 ),
		'overlay_opacity' => 30,
	)
);

/**
 * Frosted stat card items (value + two-line label).
 *
 *     add_filter( 'vm/hero_stats', fn() => [
 *         [ 'value' => '3', 'label' => 'Convention centres' ], …
 *     ] );
 */
$mcb_stats = apply_filters(
	'vm/hero_stats',
	array(
		array( 'value' => __( '3', 'vm' ), 'label' => __( 'Convention centres', 'vm' ) ),
		array( 'value' => __( '1,000s', 'vm' ), 'label' => __( 'Five-star rooms', 'vm' ) ),
		array( 'value' => __( '3h', 'vm' ), 'label' => __( 'From Europe', 'vm' ) ),
	)
);

$mcb_is_h1  = is_front_page();
$mcb_tag    = $mcb_is_h1 ? 'h1' : 'h2';
$mcb_title  = esc_html( $args['title_before'] );
$mcb_title .= $args['title_em'] ? '<span class="mcb-em">' . esc_html( $args['title_em'] ) . '</span>' : '';
$mcb_title .= esc_html( $args['title_after'] );
?>
<section class="mcb-hero" style="--mcb-hero-overlay: <?php echo esc_attr( $args['overlay_opacity'] / 100 ); ?>;">

	<div class="mcb-hero__content">
		<?php if ( $args['kicker'] ) : ?>
			<p class="mcb-eyebrow"><?php echo esc_html( $args['kicker'] ); ?></p>
		<?php endif; ?>

		<<?php echo $mcb_tag; // phpcs:ignore ?> class="mcb-hero__title"><?php echo $mcb_title; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></<?php echo $mcb_tag; // phpcs:ignore ?>>

		<?php if ( $args['description'] ) : ?>
			<p class="mcb-hero__desc"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>

		<div class="mcb-hero__actions">
			<?php if ( $args['primary_text'] ) : ?>
				<a class="mcb-btn mcb-btn--primary" href="<?php echo esc_url( $args['primary_url'] ); ?>">
					<?php echo esc_html( $args['primary_text'] ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $args['secondary_text'] ) : ?>
				<a class="mcb-btn mcb-btn--outline" href="<?php echo esc_url( $args['secondary_url'] ); ?>">
					<?php echo esc_html( $args['secondary_text'] ); ?>
					<?php mcb_icon( 'arrow-right' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( $args['show_search'] ) : ?>
			<?php mcb_part( 'components/search-bar' ); ?>
		<?php endif; ?>
	</div>

	<div class="mcb-hero__media">
		<?php
		if ( $args['image_id'] ) {
			echo wp_get_attachment_image(
				$args['image_id'],
				'mcb-hero',
				false,
				array(
					'loading'       => 'eager',
					'fetchpriority' => 'high',
				)
			);
		} elseif ( $args['image_url'] ) {
			printf( '<img src="%s" alt="" loading="eager" fetchpriority="high">', esc_url( $args['image_url'] ) );
		}
		?>
		<span class="mcb-hero__overlay" aria-hidden="true"></span>

		<div class="mcb-hero__media-caption">
			<p class="mcb-eyebrow mcb-eyebrow--on-dark"><?php echo esc_html( $args['kicker'] ); ?></p>
		</div>

		<?php if ( $args['show_stats'] && ! empty( $mcb_stats ) ) : ?>
			<dl class="mcb-hero__stats">
				<?php foreach ( $mcb_stats as $mcb_i => $mcb_stat ) : ?>
					<?php if ( $mcb_i > 0 ) : ?>
						<span class="mcb-hero__stat-divider" aria-hidden="true"></span>
					<?php endif; ?>
					<div class="mcb-hero__stat">
						<dd><b><?php echo esc_html( $mcb_stat['value'] ); ?></b></dd>
						<dt><span><?php echo esc_html( $mcb_stat['label'] ); ?></span></dt>
					</div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
	</div>

</section>
