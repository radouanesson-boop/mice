<?php
/**
 * Elementor widget: MCB Stats.
 *
 * Repeater of animated counters ("120+ venues", "40k hotel beds",
 * "8 min airport transfer"). Values, suffixes and labels are all
 * client-editable; the count-up animation lives in assets/js/main.js.
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
		$this->start_controls_section( 'section_items', array( 'label' => __( 'Statistics', 'mcb' ) ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'value',
			array(
				'label'   => __( 'Number', 'mcb' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 120,
			)
		);

		$repeater->add_control(
			'suffix',
			array(
				'label'   => __( 'Suffix', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '+',
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Event venues', 'mcb' ),
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
					array( 'value' => 120, 'suffix' => '+', 'label' => __( 'Event venues', 'mcb' ) ),
					array( 'value' => 40, 'suffix' => 'k', 'label' => __( 'Hotel beds', 'mcb' ) ),
					array( 'value' => 26, 'suffix' => '', 'label' => __( 'Direct air routes', 'mcb' ) ),
					array( 'value' => 15, 'suffix' => ' min', 'label' => __( 'Airport to medina', 'mcb' ) ),
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		mcb_part( 'sections/section-stats', array( 'items' => is_array( $s['items'] ) ? $s['items'] : array() ) );
	}
}
