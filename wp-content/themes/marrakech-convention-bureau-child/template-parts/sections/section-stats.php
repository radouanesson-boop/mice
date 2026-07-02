<?php
/**
 * Statistics band with count-up animation.
 *
 * Data source order: $args['items'] (Elementor widget) → `mcb/stats` filter
 * → sensible defaults so the section never renders empty on a fresh install.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_items = $args['items'] ?? array();

if ( empty( $mcb_items ) ) {
	$mcb_items = apply_filters(
		'mcb/stats',
		array(
			array( 'value' => 120, 'suffix' => '+', 'label' => __( 'Event venues', 'mcb' ) ),
			array( 'value' => 40, 'suffix' => 'k', 'label' => __( 'Hotel beds', 'mcb' ) ),
			array( 'value' => 26, 'suffix' => '', 'label' => __( 'Direct air routes', 'mcb' ) ),
			array( 'value' => 15, 'suffix' => ' min', 'label' => __( 'Airport to medina', 'mcb' ) ),
		)
	);
}

if ( empty( $mcb_items ) ) {
	return;
}
?>
<section class="mcb-section mcb-section--stats mcb-stats">
	<div class="mcb-container">
		<dl class="mcb-stats__grid">
			<?php foreach ( $mcb_items as $mcb_item ) : ?>
				<div class="mcb-stats__item">
					<dt class="mcb-stats__label"><?php echo esc_html( $mcb_item['label'] ?? '' ); ?></dt>
					<dd class="mcb-stats__value">
						<span class="mcb-stats__number" data-mcb-counter="<?php echo esc_attr( (string) ( $mcb_item['value'] ?? 0 ) ); ?>">
							<?php echo esc_html( number_format_i18n( (float) ( $mcb_item['value'] ?? 0 ) ) ); ?>
						</span><span class="mcb-stats__suffix"><?php echo esc_html( $mcb_item['suffix'] ?? '' ); ?></span>
					</dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>
