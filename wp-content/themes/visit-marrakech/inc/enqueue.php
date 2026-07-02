<?php
/**
 * Assets: one stylesheet, one deferred vanilla script, self-hosted fonts.
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

/**
 * mtime-based cache busting.
 */
function vm_asset_version( string $relative ): string {
	$file = VM_DIR . '/' . ltrim( $relative, '/' );

	return file_exists( $file ) ? (string) filemtime( $file ) : VM_VERSION;
}

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		wp_enqueue_style( 'vm-main', VM_URI . '/assets/css/main.css', array(), vm_asset_version( 'assets/css/main.css' ) );

		wp_enqueue_script(
			'vm-main',
			VM_URI . '/assets/js/main.js',
			array(),
			vm_asset_version( 'assets/js/main.js' ),
			array( 'in_footer' => true, 'strategy' => 'defer' )
		);

		wp_localize_script(
			'vm-main',
			'vmConfig',
			array(
				'breakpointDesktop' => 1024,
				'searchEndpoint'    => function_exists( 'rest_url' ) ? rest_url( 'dmf/v1/search' ) : '',
				'i18n'              => array(
					'openMenu'  => __( 'Open menu', 'vm' ),
					'closeMenu' => __( 'Close menu', 'vm' ),
					'noResults' => __( 'No results found.', 'vm' ),
					'sending'   => __( 'Sending…', 'vm' ),
					'sent'      => __( 'Thank you — we will come back to you within 48 hours.', 'vm' ),
					'error'     => __( 'Something went wrong. Please try again.', 'vm' ),
				),
			)
		);
	}
);

// Preload the two variable fonts.
add_action(
	'wp_head',
	static function (): void {
		foreach ( array( '/assets/fonts/jost-latin-var.woff2', '/assets/fonts/newsreader-italic-latin-var.woff2' ) as $font ) {
			if ( file_exists( VM_DIR . $font ) ) {
				printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( VM_URI . $font ) );
			}
		}
	},
	5
);
