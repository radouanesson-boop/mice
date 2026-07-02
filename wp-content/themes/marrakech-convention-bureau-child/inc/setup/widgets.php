<?php
/**
 * Widget areas for the MCB footer.
 *
 * Four columns + a pre-footer CTA strip. Content is 100% editor-managed:
 * Appearance → Widgets (or Elementor's footer location when Pro is active,
 * which takes precedence — see template-parts/footer/site-footer.php).
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'widgets_init',
	function () {
		$shared = array(
			'before_widget' => '<div id="%1$s" class="mcb-footer__widget widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="mcb-footer__widget-title">',
			'after_title'   => '</h2>',
		);

		register_sidebar(
			array(
				'name'        => __( 'MCB Footer — Brand column', 'mcb' ),
				'id'          => 'mcb-footer-brand',
				'description' => __( 'Logo, mission statement, social links.', 'mcb' ),
			) + $shared
		);

		foreach ( array( 1, 2, 3 ) as $i ) {
			register_sidebar(
				array(
					/* translators: %d: column number. */
					'name'        => sprintf( __( 'MCB Footer — Column %d', 'mcb' ), $i ),
					'id'          => 'mcb-footer-' . $i,
					'description' => __( 'Footer navigation / contact column.', 'mcb' ),
				) + $shared
			);
		}

		register_sidebar(
			array(
				'name'        => __( 'MCB Pre-footer CTA', 'mcb' ),
				'id'          => 'mcb-prefooter',
				'description' => __( 'Newsletter signup or bid-book CTA strip above the footer.', 'mcb' ),
			) + $shared
		);
	}
);
