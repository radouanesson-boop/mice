<?php
/**
 * Elementor widget: MCB Hero.
 *
 * The design-board-1A split hero as an editable widget — eyebrow, display
 * title with an italic accent word, lead, two pill CTAs, cinematic image
 * and the frosted stats card. Renders template-parts/hero/hero-home.php so
 * PHP and Elementor output stay pixel-identical.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;
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
				'label'   => __( 'Eyebrow', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Marrakech Convention Bureau', 'mcb' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'title_before',
			array(
				'label'   => __( 'Title — before accent', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Where the world meets ', 'mcb' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'title_em',
			array(
				'label'       => __( 'Title — accent word (italic serif)', 'mcb' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Marrakech', 'mcb' ),
				'description' => __( 'Rendered in Newsreader italic, sage — e.g. “Marrakech”, “Bahja”.', 'mcb' ),
			)
		);

		$this->add_control(
			'title_after',
			array(
				'label'   => __( 'Title — after accent', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'description',
			array(
				'label'   => __( 'Lead paragraph', 'mcb' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 4,
				'default' => __( 'Africa’s rising capital for meetings, incentives, congresses and events — supported end to end by the Marrakech Convention Bureau.', 'mcb' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'primary_cta_text',
			array(
				'label'   => __( 'Primary button', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Submit an RFP', 'mcb' ),
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
				'default' => __( 'Explore the destination', 'mcb' ),
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

		$this->end_controls_section();

		$this->start_controls_section(
			'section_media',
			array( 'label' => __( 'Image & stats', 'mcb' ) )
		);

		$this->add_control(
			'image',
			array(
				'label'   => __( 'Hero image', 'mcb' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => Utils::get_placeholder_image_src() ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'overlay_opacity',
			array(
				'label'   => __( 'Image scrim strength', 'mcb' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default' => array( 'size' => 30 ),
			)
		);

		$this->add_control(
			'show_stats',
			array(
				'label'        => __( 'Show frosted stats card', 'mcb' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'value',
			array(
				'label'   => __( 'Value', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '3',
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'mcb' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Convention centres', 'mcb' ),
			)
		);

		$this->add_control(
			'stats',
			array(
				'label'       => __( 'Stats', 'mcb' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ value }}} — {{{ label }}}',
				'condition'   => array( 'show_stats' => 'yes' ),
				'default'     => array(
					array( 'value' => '3', 'label' => __( 'Convention centres', 'mcb' ) ),
					array( 'value' => '1,000s', 'label' => __( 'Five-star rooms', 'mcb' ) ),
					array( 'value' => '3h', 'label' => __( 'From Europe', 'mcb' ) ),
				),
			)
		);

		$this->add_control(
			'show_search',
			array(
				'label'        => __( 'Show listing search bar', 'mcb' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$stats = array();
		foreach ( (array) $s['stats'] as $stat ) {
			if ( ! empty( $stat['value'] ) ) {
				$stats[] = array(
					'value' => $stat['value'],
					'label' => isset( $stat['label'] ) ? $stat['label'] : '',
				);
			}
		}

		if ( ! empty( $stats ) ) {
			add_filter( 'mcb/hero_stats', $stats_cb = static fn() => $stats );
		}

		mcb_part(
			'hero/hero-home',
			array(
				'kicker'          => $s['kicker'],
				'title_before'    => $s['title_before'],
				'title_em'        => $s['title_em'],
				'title_after'     => $s['title_after'],
				'description'     => $s['description'],
				'primary_text'    => $s['primary_cta_text'],
				'primary_url'     => ! empty( $s['primary_cta_link']['url'] ) ? $s['primary_cta_link']['url'] : '',
				'secondary_text'  => $s['secondary_cta_text'],
				'secondary_url'   => ! empty( $s['secondary_cta_link']['url'] ) ? $s['secondary_cta_link']['url'] : '',
				'show_search'     => 'yes' === $s['show_search'],
				'show_stats'      => 'yes' === $s['show_stats'],
				'image_url'       => ! empty( $s['image']['url'] ) ? $s['image']['url'] : '',
				'image_id'        => ! empty( $s['image']['id'] ) ? (int) $s['image']['id'] : 0,
				'overlay_opacity' => isset( $s['overlay_opacity']['size'] ) ? (int) $s['overlay_opacity']['size'] : 30,
			)
		);

		if ( ! empty( $stats ) ) {
			remove_filter( 'mcb/hero_stats', $stats_cb );
		}
	}
}
