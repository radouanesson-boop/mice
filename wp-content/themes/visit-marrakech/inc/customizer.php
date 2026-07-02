<?php
/**
 * Customizer: hero, CTA and bureau content — every homepage string is
 * editable without code.
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'customize_register',
	static function ( WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section(
			'vm_homepage',
			array(
				'title'    => __( 'Homepage content', 'vm' ),
				'priority' => 30,
			)
		);

		$fields = array(
			'vm_hero_title_before' => array( __( 'Hero title — before accent', 'vm' ), 'text' ),
			'vm_hero_title_em'     => array( __( 'Hero title — accent word (italic)', 'vm' ), 'text' ),
			'vm_hero_title_after'  => array( __( 'Hero title — after accent', 'vm' ), 'text' ),
			'vm_hero_description'  => array( __( 'Hero lead paragraph', 'vm' ), 'textarea' ),
			'vm_hero_image'        => array( __( 'Hero image', 'vm' ), 'media' ),
			'vm_cta_title'         => array( __( 'Final CTA title', 'vm' ), 'text' ),
			'vm_cta_description'   => array( __( 'Final CTA lead', 'vm' ), 'textarea' ),
			'vm_cta_image'         => array( __( 'Final CTA background image', 'vm' ), 'media' ),
			'vm_rfp_url'           => array( __( 'RFP page URL', 'vm' ), 'text' ),
			'vm_footer_tagline'    => array( __( 'Footer tagline', 'vm' ), 'text' ),
		);

		foreach ( $fields as $key => [ $label, $type ] ) {
			$wp_customize->add_setting(
				$key,
				array(
					'sanitize_callback' => 'media' === $type ? 'absint' : ( 'textarea' === $type ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
				)
			);

			if ( 'media' === $type ) {
				$wp_customize->add_control(
					new WP_Customize_Media_Control( $wp_customize, $key, array( 'label' => $label, 'section' => 'vm_homepage', 'mime_type' => 'image' ) )
				);
			} else {
				$wp_customize->add_control( $key, array( 'label' => $label, 'section' => 'vm_homepage', 'type' => $type ) );
			}
		}
	}
);

/**
 * The site-wide RFP CTA (header, mobile drawer, CTA band).
 *
 * @return array{text: string, url: string}
 */
function vm_rfp_cta(): array {
	return apply_filters(
		'vm/rfp_cta',
		array(
			'text' => __( 'Submit an RFP', 'vm' ),
			'url'  => get_theme_mod( 'vm_rfp_url', home_url( '/submit-rfp/' ) ),
		)
	);
}
