<?php
/**
 * FAQ accordion — semantic <details>/<summary>, zero JS required
 * (assets/js/main.js only adds the exclusive-open nicety).
 *
 * @param array $args {
 *     @type string $title Heading.
 *     @type array  $items [ [ 'q' => '…', 'a' => '…' ], … ] — pass from a
 *                        page builder loop or ACF repeater; empty = skip.
 * }
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'title' => __( 'Frequently asked questions', 'dmf' ),
		'items' => array(),
	)
);

$dmf_items = apply_filters( 'dmf/faq_items', $args['items'] );

if ( empty( $dmf_items ) ) {
	return;
}
?>
<section class="dmf-section dmf-section--faq">
	<div class="dmf-container dmf-container--narrow">

		<?php dmf_part( 'components/section-heading', array( 'title' => $args['title'] ) ); ?>

		<div class="dmf-accordion" data-dmf-accordion>
			<?php foreach ( $dmf_items as $dmf_item ) : ?>
				<details class="dmf-accordion__item">
					<summary class="dmf-accordion__summary">
						<span><?php echo esc_html( $dmf_item['q'] ?? '' ); ?></span>
						<?php dmf_icon( 'chevron-down' ); ?>
					</summary>
					<div class="dmf-accordion__content">
						<?php echo wp_kses_post( wpautop( $dmf_item['a'] ?? '' ) ); ?>
					</div>
				</details>
			<?php endforeach; ?>
		</div>

	</div>
</section>
