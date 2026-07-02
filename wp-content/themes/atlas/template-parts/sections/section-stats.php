<?php
/**
 * Success stats — board 1A "Trusted at the highest level": hairline-top
 * columns with reference events/figures, followed (optionally) by the
 * featured pull-quote (section-testimonials).
 *
 * Data source order: $args['items'] (Elementor widget) → `dmf/stats` filter
 * → design defaults so the section never renders empty.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker' => __( 'Proven ground', 'dmf' ),
		'title'  => __( 'Trusted at the highest level', 'dmf' ),
		'items'  => array(),
		'quote'  => true,
	)
);

$dmf_items = $args['items'];

if ( empty( $dmf_items ) ) {
	$dmf_items = apply_filters(
		'dmf/stats',
		array(
			array( 'value' => 'COP22', 'label' => __( 'UN Climate Change Conference', 'dmf' ) ),
			array( 'value' => 'IMF · World Bank', 'label' => __( 'Annual Meetings 2023', 'dmf' ) ),
			array( 'value' => 'GITEX Africa', 'label' => __( 'Largest tech event in Africa', 'dmf' ) ),
			array( 'value' => 'PURE Life', 'label' => __( 'Luxury experiential travel', 'dmf' ) ),
		)
	);
}

if ( empty( $dmf_items ) ) {
	return;
}
?>
<section class="dmf-section dmf-section--stats dmf-stats">
	<div class="dmf-container">

		<?php
		dmf_part(
			'components/section-heading',
			array(
				'kicker' => $args['kicker'],
				'title'  => $args['title'],
			)
		);
		?>

		<dl class="dmf-stats__grid">
			<?php foreach ( $dmf_items as $dmf_item ) : ?>
				<div class="dmf-stats__item">
					<dt class="dmf-stats__label"><?php echo esc_html( $dmf_item['label'] ?? '' ); ?></dt>
					<dd class="dmf-stats__value">
						<?php if ( isset( $dmf_item['value'] ) && ! is_numeric( $dmf_item['value'] ) ) : ?>
							<?php echo esc_html( $dmf_item['value'] ); ?>
						<?php else : ?>
							<span data-dmf-counter="<?php echo esc_attr( (string) ( $dmf_item['value'] ?? 0 ) ); ?>">
								<?php echo esc_html( number_format_i18n( (float) ( $dmf_item['value'] ?? 0 ) ) ); ?>
							</span><?php echo esc_html( $dmf_item['suffix'] ?? '' ); ?>
						<?php endif; ?>
					</dd>
				</div>
			<?php endforeach; ?>
		</dl>

		<?php if ( $args['quote'] ) : ?>
			<?php dmf_part( 'sections/section-testimonials' ); ?>
		<?php endif; ?>

	</div>
</section>
