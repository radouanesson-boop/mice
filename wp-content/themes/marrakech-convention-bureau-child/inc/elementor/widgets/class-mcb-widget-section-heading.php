<?php
/**
 * Elementor widget: MCB Section Heading.
 *
 * Kicker + title + intro, with alignment — the standard section opener
 * used across the homepage so all sections share one heading rhythm.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Class MCB_Widget_Section_Heading
 */
class MCB_Widget_Section_Heading extends \Elementor\Widget_Base {

	public function get_name() {
		return 'mcb-section-heading';
	}

	public function get_title() {
		return __( 'MCB Section Heading', 'mcb' );
	}

	public function get_icon() {
		return 'eicon-heading';
	}

	public function get_categories() {
		return array( 'mcb' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'mcb' ) ) );

		$this->add_control(
			'kicker',
			array(
				'label'   => __( 'Kicker', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Discover', 'mcb' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Exceptional venues', 'mcb' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'heading_tag',
			array(
				'label'   => __( 'HTML tag', 'mcb' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array_combine( array( 'h1', 'h2', 'h3', 'h4' ), array( 'H1', 'H2', 'H3', 'H4' ) ),
				'default' => 'h2',
			)
		);

		$this->add_control(
			'intro',
			array(
				'label'   => __( 'Intro text', 'mcb' ),
				'type'    => Controls_Manager::TEXTAREA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'align',
			array(
				'label'   => __( 'Alignment', 'mcb' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'left'   => array( 'title' => __( 'Left', 'mcb' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => __( 'Center', 'mcb' ), 'icon' => 'eicon-text-align-center' ),
				),
				'default' => 'center',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		mcb_part(
			'components/section-heading',
			array(
				'kicker' => $s['kicker'],
				'title'  => $s['title'],
				'tag'    => $s['heading_tag'],
				'intro'  => $s['intro'],
				'align'  => $s['align'],
			)
		);
	}
}
