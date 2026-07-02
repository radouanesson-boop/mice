<?php
/**
 * Elementor widget: MCB Hero.
 *
 * The homepage hero as an editable widget — headline, kicker, copy, two
 * CTAs, background image/overlay and the integrated search bar toggle.
 * Renders template-parts/hero/hero-home.php so PHP and Elementor output
 * are pixel-identical.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Utils;

/**
 * Class MCB_Widget_Hero
 */
class MCB_Widget_Hero extends \Elementor\Widget_Base {

	public function get_name() {
		return 'mcb-hero';
	}

	public function get_title() {
		return __( 'MCB Hero', 'mcb' );
	}

	public function get_icon() {
		return 'eicon-banner';
	}

	public function get_categories() {
		return array( 'mcb' );
	}

	public function get_keywords() {
		return array( 'hero', 'banner', 'marrakech', 'header' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array( 'label' => __( 'Content', 'mcb' ) )
		);

		$this->add_control(
			'kicker',
			array(
				'label'   => __( 'Kicker (small line above title)', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Marrakech Convention Bureau', 'mcb' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'mcb' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => __( 'Where world-class events meet timeless wonder', 'mcb' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'description',
			array(
				'label'   => __( 'Description', 'mcb' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 4,
				'default' => __( 'Your trusted partner for meetings, incentives, conferences and exhibitions in the Red City.', 'mcb' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'primary_cta_text',
			array(
				'label'   => __( 'Primary button', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Plan your event', 'mcb' ),
			)
		);

		$this->add_control(
			'primary_cta_link',
			array(
				'label'   => __( 'Primary button link', 'mcb' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'secondary_cta_text',
			array(
				'label'   => __( 'Secondary button', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Explore venues', 'mcb' ),
			)
		);

		$this->add_control(
			'secondary_cta_link',
			array(
				'label'   => __( 'Secondary button link', 'mcb' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'show_search',
			array(
				'label'        => __( 'Show listing search bar', 'mcb' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_background',
			array( 'label' => __( 'Background', 'mcb' ) )
		);

		$this->add_control(
			'image',
			array(
				'label'   => __( 'Background image', 'mcb' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => Utils::get_placeholder_image_src() ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'overlay_opacity',
			array(
				'label'   => __( 'Overlay strength', 'mcb' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default' => array( 'size' => 55 ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		mcb_part(
			'hero/hero-home',
			array(
				'kicker'          => $s['kicker'],
				'title'           => $s['title'],
				'description'     => $s['description'],
				'primary_text'    => $s['primary_cta_text'],
				'primary_url'     => ! empty( $s['primary_cta_link']['url'] ) ? $s['primary_cta_link']['url'] : '',
				'secondary_text'  => $s['secondary_cta_text'],
				'secondary_url'   => ! empty( $s['secondary_cta_link']['url'] ) ? $s['secondary_cta_link']['url'] : '',
				'show_search'     => 'yes' === $s['show_search'],
				'image_url'       => ! empty( $s['image']['url'] ) ? $s['image']['url'] : '',
				'image_id'        => ! empty( $s['image']['id'] ) ? (int) $s['image']['id'] : 0,
				'overlay_opacity' => isset( $s['overlay_opacity']['size'] ) ? (int) $s['overlay_opacity']['size'] : 55,
			)
		);
	}
}
