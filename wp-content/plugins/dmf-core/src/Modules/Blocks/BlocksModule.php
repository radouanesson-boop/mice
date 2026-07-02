<?php
/**
 * Blocks module — dynamic Gutenberg blocks.
 *
 * @package DMF
 */

namespace DMF\Modules\Blocks;

use DMF\Modules\AbstractModule;
use DMF\Support\Template;

defined( 'ABSPATH' ) || exit;

/**
 * Server-rendered blocks that reuse the exact front-end templates, so the
 * editor composes pages from real framework components — no page builder
 * required. Each block renders a theme-overridable template.
 */
final class BlocksModule extends AbstractModule {

	public function register(): void {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_filter( 'block_categories_all', array( $this, 'category' ) );
	}

	public function category( array $categories ): array {
		return array_merge(
			array(
				array(
					'slug'  => 'dmf',
					'title' => __( 'Destination Framework', 'dmf' ),
				),
			),
			$categories
		);
	}

	public function register_blocks(): void {
		$blocks = array(
			'listings-grid' => array(
				'title'      => __( 'DMF Listings Grid', 'dmf' ),
				'attributes' => array(
					'type'     => array( 'type' => 'string', 'default' => '' ),
					'count'    => array( 'type' => 'number', 'default' => 6 ),
					'columns'  => array( 'type' => 'number', 'default' => 3 ),
					'taxonomy' => array( 'type' => 'string', 'default' => '' ),
					'terms'    => array( 'type' => 'string', 'default' => '' ),
				),
			),
			'search-bar'    => array(
				'title'      => __( 'DMF Search Bar', 'dmf' ),
				'attributes' => array(
					'placeholder' => array( 'type' => 'string', 'default' => '' ),
					'types'       => array( 'type' => 'string', 'default' => '' ),
				),
			),
			'inquiry-form'  => array(
				'title'      => __( 'DMF Inquiry / RFP Form', 'dmf' ),
				'attributes' => array(
					'listingId' => array( 'type' => 'number', 'default' => 0 ),
				),
			),
			'events-list'   => array(
				'title'      => __( 'DMF Upcoming Events', 'dmf' ),
				'attributes' => array(
					'count' => array( 'type' => 'number', 'default' => 3 ),
				),
			),
		);

		foreach ( $blocks as $name => $config ) {
			register_block_type(
				'dmf/' . $name,
				array(
					'api_version'     => 3,
					'title'           => $config['title'],
					'category'        => 'dmf',
					'attributes'      => $config['attributes'],
					'render_callback' => static fn( array $attributes ) => Template::render( 'blocks/' . $name, $attributes, false ),
					'supports'        => array( 'align' => array( 'wide', 'full' ) ),
				)
			);
		}
	}
}
