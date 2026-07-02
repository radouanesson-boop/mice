<?php
/**
 * Elementor integration.
 *
 *  - Registers theme-builder locations (header/footer) so the client can
 *    replace either with an Elementor Pro template — zero code.
 *  - Registers the "Marrakech CB" widget category and the MCB widgets.
 *  - Every widget outputs the same template-parts the PHP theme uses, so
 *    Elementor pages and PHP pages stay visually identical by construction.
 *
 * Everything is guarded: without Elementor the theme works fine.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

// Theme-builder locations (Elementor Pro).
add_action(
	'elementor/theme/register_locations',
	function ( $manager ) {
		$manager->register_location( 'header' );
		$manager->register_location( 'footer' );
	}
);

// Widget category.
add_action(
	'elementor/elements/categories_registered',
	function ( $manager ) {
		$manager->add_category(
			'mcb',
			array(
				'title' => __( 'Marrakech CB', 'mcb' ),
				'icon'  => 'fa fa-star',
			)
		);
	}
);

// Widgets.
add_action(
	'elementor/widgets/register',
	function ( $manager ) {
		$widgets = array(
			'class-mcb-widget-hero.php'            => 'MCB_Widget_Hero',
			'class-mcb-widget-section-heading.php' => 'MCB_Widget_Section_Heading',
			'class-mcb-widget-stats.php'           => 'MCB_Widget_Stats',
			'class-mcb-widget-listings-grid.php'   => 'MCB_Widget_Listings_Grid',
		);

		foreach ( $widgets as $file => $class ) {
			$path = MCB_DIR . '/inc/elementor/widgets/' . $file;

			if ( file_exists( $path ) ) {
				require_once $path;

				if ( class_exists( $class ) ) {
					$manager->register( new $class() );
				}
			}
		}
	}
);

// Load MCB styles inside the Elementor editor canvas so previews match.
add_action(
	'elementor/editor/before_enqueue_styles',
	function () {
		wp_enqueue_style( 'mcb-main-editor', MCB_URI . '/assets/css/main.css', array(), mcb_asset_version( 'assets/css/main.css' ) );
	}
);
