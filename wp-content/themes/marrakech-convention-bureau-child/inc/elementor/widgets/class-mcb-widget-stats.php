<?php
/**
 * Elementor widget: MCB Stats.
 *
 * The "Trusted at the highest level" reference band — hairline-top columns
 * with a value (text like "COP22" or a number that counts up) and a label.
 * Renders template-parts/sections/section-stats.php.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class MCB_Widget_Stats
 */
class MCB_Widget_Stats extends \Elementor\Widget_Base {

	public function get_name() {
		return 'mcb-stats';
	}

	public function get_title() {
		return __( 'MCB Stats', 'mcb' );
	}

	public function get_icon() {
		return 'eicon-counter';
	}

	public function get_categories() {
		return array( 'mcb' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_items', array( 'label' => __( 'Content', 'mcb' ) ) );

		$this->add_control(
			'kicker',
			array(
				'label'   => __( 'Eyebrow', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Proven ground', 'mcb' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Trusted at the highest level', 'mcb' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'value',
			array(
				'label'       => __( 'Value', 'mcb' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'COP22',
				'description' => __( 'Text ("COP22") or a number (counts up on scroll).', 'mcb' ),
			)
		);

		$repeater->add_control(
			'suffix',
			array(
				'label'   => __( 'Suffix (numbers only)', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'UN Climate Change Conference', 'mcb' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'mcb' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ value }}}{{{ suffix }}} — {{{ label }}}',
				'default'     => array(
					array( 'value' => 'COP22', 'suffix' => '', 'label' => __( 'UN Climate Change Conference', 'mcb' ) ),
					array( 'value' => 'IMF · World Bank', 'suffix' => '', 'label' => __( 'Annual Meetings 2023', 'mcb' ) ),
					array( 'value' => 'GITEX Africa', 'suffix' => '', 'label' => __( 'Largest tech event in Africa', 'mcb' ) ),
					array( 'value' => 'PURE Life', 'suffix' => '', 'label' => __( 'Luxury experiential travel', 'mcb' ) ),
				),
			)
		);

		$this->add_control(
			'show_quote',
			array(
				'label'        => __( 'Show featured testimonial quote', 'mcb' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		mcb_part(
			'sections/section-stats',
			array(
				'kicker' => $s['kicker'],
				'title'  => $s['title'],
				'items'  => is_array( $s['items'] ) ? $s['items'] : array(),
				'quote'  => 'yes' === $s['show_quote'],
			)
		);
	}
}
