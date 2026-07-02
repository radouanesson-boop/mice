<?php
/**
 * Success stats — board 1A "Trusted at the highest level": hairline-top
 * columns with reference events/figures, followed (optionally) by the
 * featured pull-quote (section-testimonials).
 *
 * Data source order: $args['items'] (Elementor widget) → `mcb/stats` filter
 * → design defaults so the section never renders empty.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker' => __( 'Proven ground', 'mcb' ),
		'title'  => __( 'Trusted at the highest level', 'mcb' ),
		'items'  => array(),
		'quote'  => true,
	)
);

$mcb_items = $args['items'];

if ( empty( $mcb_items ) ) {
	$mcb_items = apply_filters(
		'mcb/stats',
		array(
			array( 'value' => 'COP22', 'label' => __( 'UN Climate Change Conference', 'mcb' ) ),
			array( 'value' => 'IMF · World Bank', 'label' => __( 'Annual Meetings 2023', 'mcb' ) ),
			array( 'value' => 'GITEX Africa', 'label' => __( 'Largest tech event in Africa', 'mcb' ) ),
			array( 'value' => 'PURE Life', 'label' => __( 'Luxury experiential travel', 'mcb' ) ),
		)
	);
}

if ( empty( $mcb_items ) ) {
	return;
}
?>
<section class="mcb-section mcb-section--stats mcb-stats">
	<div class="mcb-container">

		<?php
		mcb_part(
			'components/section-heading',
			array(
				'kicker' => $args['kicker'],
				'title'  => $args['title'],
			)
		);
		?>

		<dl class="mcb-stats__grid">
			<?php foreach ( $mcb_items as $mcb_item ) : ?>
				<div class="mcb-stats__item">
					<dt class="mcb-stats__label"><?php echo esc_html( $mcb_item['label'] ?? '' ); ?></dt>
					<dd class="mcb-stats__value">
						<?php if ( isset( $mcb_item['value'] ) && ! is_numeric( $mcb_item['value'] ) ) : ?>
							<?php echo esc_html( $mcb_item['value'] ); ?>
						<?php else : ?>
							<span data-mcb-counter="<?php echo esc_attr( (string) ( $mcb_item['value'] ?? 0 ) ); ?>">
								<?php echo esc_html( number_format_i18n( (float) ( $mcb_item['value'] ?? 0 ) ) ); ?>
							</span><?php echo esc_html( $mcb_item['suffix'] ?? '' ); ?>
						<?php endif; ?>
					</dd>
				</div>
			<?php endforeach; ?>
		</dl>

		<?php if ( $args['quote'] ) : ?>
			<?php mcb_part( 'sections/section-testimonials' ); ?>
		<?php endif; ?>

	</div>
</section>
