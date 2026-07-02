<?php
/**
 * Menu locations + footer widget areas (design board 1A footer).
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		register_nav_menus(
			array(
				'vm_primary'      => __( 'Primary (desktop, supports mega menu via "mcb-mega" class)', 'vm' ),
				'vm_mobile'       => __( 'Mobile drawer (falls back to Primary)', 'vm' ),
				'vm_footer_1'     => __( 'Footer — Explore column', 'vm' ),
				'vm_footer_2'     => __( 'Footer — Plan column', 'vm' ),
				'vm_footer_3'     => __( 'Footer — Extra column', 'vm' ),
				'vm_footer_legal' => __( 'Footer — Legal bar', 'vm' ),
			)
		);
	}
);

add_action(
	'widgets_init',
	static function (): void {
		$shared = array(
			'before_widget' => '<div id="%1$s" class="mcb-footer__widget widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="mcb-footer__widget-title">',
			'after_title'   => '</h2>',
		);

		register_sidebar(
			array(
				'name'        => __( 'Footer — Brand column', 'vm' ),
				'id'          => 'vm-footer-brand',
				'description' => __( 'Logo lockup, mission statement.', 'vm' ),
			) + $shared
		);

		foreach ( array( 1, 2, 3 ) as $i ) {
			register_sidebar(
				array(
					/* translators: %d: column number. */
					'name'        => sprintf( __( 'Footer — Column %d', 'vm' ), $i ),
					'id'          => 'vm-footer-' . $i,
					'description' => __( 'Footer navigation / newsletter column.', 'vm' ),
				) + $shared
			);
		}
	}
);
