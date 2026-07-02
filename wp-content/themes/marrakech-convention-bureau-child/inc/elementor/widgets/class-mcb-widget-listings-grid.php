<?php
/**
 * Elementor widget: MCB Listings Grid.
 *
 * Dynamic grid of Routiz listings rendered with the MCB card components.
 * The client picks a card style (venue / hotel / experience / event /
 * auto), a taxonomy filter and a count — content stays 100% dynamic.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Class MCB_Widget_Listings_Grid
 */
class MCB_Widget_Listings_Grid extends \Elementor\Widget_Base {

	public function get_name() {
		return 'mcb-listings-grid';
	}

	public function get_title() {
		return __( 'MCB Listings Grid', 'mcb' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return array( 'mcb' );
	}

	public function get_keywords() {
		return array( 'listing', 'venue', 'hotel', 'experience', 'event', 'grid' );
	}

	/**
	 * Public taxonomies attached to listing post types, for the select control.
	 *
	 * @return array slug => label.
	 */
	protected function listing_taxonomies() {
		$options = array( '' => __( '— All listings —', 'mcb' ) );

		foreach ( mcb_listing_post_types() as $post_type ) {
			foreach ( get_object_taxonomies( $post_type, 'objects' ) as $taxonomy ) {
				if ( $taxonomy->public ) {
					$options[ $taxonomy->name ] = $taxonomy->label;
				}
			}
		}

		return $options;
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_query', array( 'label' => __( 'Query', 'mcb' ) ) );

		$this->add_control(
			'card_style',
			array(
				'label'   => __( 'Card style', 'mcb' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'       => __( 'Auto (detect from listing type)', 'mcb' ),
					'venue'      => __( 'Venue', 'mcb' ),
					'hotel'      => __( 'Hotel', 'mcb' ),
					'experience' => __( 'Experience', 'mcb' ),
					'event'      => __( 'Event', 'mcb' ),
				),
			)
		);

		$this->add_control(
			'taxonomy',
			array(
				'label'   => __( 'Filter by taxonomy', 'mcb' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->listing_taxonomies(),
				'default' => '',
			)
		);

		$this->add_control(
			'terms',
			array(
				'label'       => __( 'Term slugs (comma-separated)', 'mcb' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'e.g. conference-centers, palaces', 'mcb' ),
				'condition'   => array( 'taxonomy!' => '' ),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'   => __( 'Number of listings', 'mcb' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 24,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order by', 'mcb' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'       => __( 'Newest', 'mcb' ),
					'title'      => __( 'Title', 'mcb' ),
					'rand'       => __( 'Random', 'mcb' ),
					'menu_order' => __( 'Manual order', 'mcb' ),
				),
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'   => __( 'Columns (desktop)', 'mcb' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '3',
				'options' => array( '2' => '2', '3' => '3', '4' => '4' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$query = mcb_listing_query(
			array(
				'count'    => (int) $s['count'],
				'taxonomy' => $s['taxonomy'],
				'terms'    => array_filter( array_map( 'trim', explode( ',', (string) $s['terms'] ) ) ),
				'orderby'  => $s['orderby'],
			)
		);

		if ( ! $query->have_posts() ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p class="mcb-notice">' . esc_html__( 'No listings found — publish listings or adjust the query.', 'mcb' ) . '</p>';
			}
			return;
		}

		printf( '<div class="mcb-grid mcb-grid--cols-%s">', esc_attr( $s['columns'] ) );

		while ( $query->have_posts() ) {
			$query->the_post();

			$kind = 'auto' === $s['card_style'] ? mcb_listing_kind() : $s['card_style'];

			mcb_part( 'cards/card-' . sanitize_file_name( $kind ), array( 'post_id' => get_the_ID() ) );
		}

		echo '</div>';

		wp_reset_postdata();
	}
}
