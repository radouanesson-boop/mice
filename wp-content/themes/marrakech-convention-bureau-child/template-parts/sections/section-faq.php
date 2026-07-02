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
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'title' => __( 'Frequently asked questions', 'mcb' ),
		'items' => array(),
	)
);

$mcb_items = apply_filters( 'mcb/faq_items', $args['items'] );

if ( empty( $mcb_items ) ) {
	return;
}
?>
<section class="mcb-section mcb-section--faq">
	<div class="mcb-container mcb-container--narrow">

		<?php mcb_part( 'components/section-heading', array( 'title' => $args['title'] ) ); ?>

		<div class="mcb-accordion" data-mcb-accordion>
			<?php foreach ( $mcb_items as $mcb_item ) : ?>
				<details class="mcb-accordion__item">
					<summary class="mcb-accordion__summary">
						<span><?php echo esc_html( $mcb_item['q'] ?? '' ); ?></span>
						<?php mcb_icon( 'chevron-down' ); ?>
					</summary>
					<div class="mcb-accordion__content">
						<?php echo wp_kses_post( wpautop( $mcb_item['a'] ?? '' ) ); ?>
					</div>
				</details>
			<?php endforeach; ?>
		</div>

	</div>
</section>
