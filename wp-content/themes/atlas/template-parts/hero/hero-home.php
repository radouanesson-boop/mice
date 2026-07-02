<?php
/**
 * Homepage hero — design board 1A: split layout. Left: eyebrow, light
 * display title with an italic Newsreader accent word, lead, two pill CTAs.
 * Right: cinematic image with soft gradient and a frosted stats card.
 * Mobile (board 1C): image on top with the title over it, stacked buttons,
 * 3-up stat row.
 *
 * Every field comes from $args (Elementor widget / page template) with
 * theme-mod defaults — nothing hardcoded.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'          => get_theme_mod( 'dmf_hero_kicker', __( 'Marrakech Convention Bureau', 'dmf' ) ),
		// Title in three parts so the accent word renders in Newsreader italic.
		'title_before'    => get_theme_mod( 'dmf_hero_title_before', __( 'Where the world meets ', 'dmf' ) ),
		'title_em'        => get_theme_mod( 'dmf_hero_title_em', __( 'Marrakech', 'dmf' ) ),
		'title_after'     => get_theme_mod( 'dmf_hero_title_after', '' ),
		'description'     => get_theme_mod( 'dmf_hero_description', __( 'Africa’s rising capital for meetings, incentives, congresses and events — supported end to end by the Marrakech Convention Bureau.', 'dmf' ) ),
		'primary_text'    => __( 'Submit an RFP', 'dmf' ),
		'primary_url'     => home_url( '/submit-rfp/' ),
		'secondary_text'  => __( 'Explore the destination', 'dmf' ),
		'secondary_url'   => home_url( '/destination/' ),
		'show_search'     => false,
		'show_stats'      => true,
		'image_url'       => '',
		'image_id'        => (int) get_theme_mod( 'dmf_hero_image', 0 ),
		'overlay_opacity' => 30,
	)
);

/**
 * Frosted stat card items (value + two-line label).
 *
 *     add_filter( 'dmf/hero_stats', fn() => [
 *         [ 'value' => '3', 'label' => 'Convention centres' ], …
 *     ] );
 */
$dmf_stats = apply_filters(
	'dmf/hero_stats',
	array(
		array( 'value' => __( '3', 'dmf' ), 'label' => __( 'Convention centres', 'dmf' ) ),
		array( 'value' => __( '1,000s', 'dmf' ), 'label' => __( 'Five-star rooms', 'dmf' ) ),
		array( 'value' => __( '3h', 'dmf' ), 'label' => __( 'From Europe', 'dmf' ) ),
	)
);

$dmf_is_h1  = is_front_page();
$dmf_tag    = $dmf_is_h1 ? 'h1' : 'h2';
$dmf_title  = esc_html( $args['title_before'] );
$dmf_title .= $args['title_em'] ? '<span class="dmf-em">' . esc_html( $args['title_em'] ) . '</span>' : '';
$dmf_title .= esc_html( $args['title_after'] );
?>
<section class="dmf-hero" style="--dmf-hero-overlay: <?php echo esc_attr( $args['overlay_opacity'] / 100 ); ?>;">

	<div class="dmf-hero__content">
		<?php if ( $args['kicker'] ) : ?>
			<p class="dmf-eyebrow"><?php echo esc_html( $args['kicker'] ); ?></p>
		<?php endif; ?>

		<<?php echo $dmf_tag; // phpcs:ignore ?> class="dmf-hero__title"><?php echo $dmf_title; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></<?php echo $dmf_tag; // phpcs:ignore ?>>

		<?php if ( $args['description'] ) : ?>
			<p class="dmf-hero__desc"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>

		<div class="dmf-hero__actions">
			<?php if ( $args['primary_text'] ) : ?>
				<a class="dmf-btn dmf-btn--primary" href="<?php echo esc_url( $args['primary_url'] ); ?>">
					<?php echo esc_html( $args['primary_text'] ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $args['secondary_text'] ) : ?>
				<a class="dmf-btn dmf-btn--outline" href="<?php echo esc_url( $args['secondary_url'] ); ?>">
					<?php echo esc_html( $args['secondary_text'] ); ?>
					<?php dmf_icon( 'arrow-right' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( $args['show_search'] ) : ?>
			<?php dmf_part( 'components/search-bar' ); ?>
		<?php endif; ?>
	</div>

	<div class="dmf-hero__media">
		<?php
		if ( $args['image_id'] ) {
			echo wp_get_attachment_image(
				$args['image_id'],
				'dmf-hero',
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
		<span class="dmf-hero__overlay" aria-hidden="true"></span>

		<div class="dmf-hero__media-caption">
			<p class="dmf-eyebrow dmf-eyebrow--on-dark"><?php echo esc_html( $args['kicker'] ); ?></p>
		</div>

		<?php if ( $args['show_stats'] && ! empty( $dmf_stats ) ) : ?>
			<dl class="dmf-hero__stats">
				<?php foreach ( $dmf_stats as $dmf_i => $dmf_stat ) : ?>
					<?php if ( $dmf_i > 0 ) : ?>
						<span class="dmf-hero__stat-divider" aria-hidden="true"></span>
					<?php endif; ?>
					<div class="dmf-hero__stat">
						<dd><b><?php echo esc_html( $dmf_stat['value'] ); ?></b></dd>
						<dt><span><?php echo esc_html( $dmf_stat['label'] ); ?></span></dt>
					</div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
	</div>

</section>
