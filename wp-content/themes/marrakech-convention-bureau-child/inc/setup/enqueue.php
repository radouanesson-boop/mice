<?php
/**
 * Asset loading.
 *
 * Strategy:
 *  - Parent (Brikk) assets load untouched — its JS powers maps, filters,
 *    galleries and the dashboard.
 *  - One compiled child stylesheet (assets/css/main.css) loads AFTER every
 *    Brikk style so the MCB skin always wins the cascade without !important.
 *  - One small vanilla JS file, deferred, no jQuery dependency.
 *  - Fonts are self-hosted (assets/fonts) and preloaded.
 *  - Cache-busting via filemtime so deploys invalidate automatically.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Version string for a theme asset based on its modification time.
 *
 * @param string $relative Path relative to the child theme root.
 * @return string
 */
function mcb_asset_version( $relative ) {
	$file = MCB_DIR . '/' . ltrim( $relative, '/' );
	return file_exists( $file ) ? (string) filemtime( $file ) : MCB_VERSION;
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$parent_handle = 'brikk-style';

		// Parent stylesheet (Brikk enqueues its own; this is a safety net and
		// gives us a stable dependency handle).
		if ( ! wp_style_is( $parent_handle, 'registered' ) && ! wp_style_is( $parent_handle, 'enqueued' ) ) {
			wp_enqueue_style( $parent_handle, get_template_directory_uri() . '/style.css', array(), wp_get_theme( get_template() )->get( 'Version' ) );
		}

		// MCB design system — depends on parent so it always loads after.
		wp_enqueue_style(
			'mcb-main',
			MCB_URI . '/assets/css/main.css',
			array( $parent_handle ),
			mcb_asset_version( 'assets/css/main.css' )
		);

		// Behaviour: mobile nav, mega menu, sticky header, counters, accordion.
		wp_enqueue_script(
			'mcb-main',
			MCB_URI . '/assets/js/main.js',
			array(), // Vanilla JS — no jQuery.
			mcb_asset_version( 'assets/js/main.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		wp_localize_script(
			'mcb-main',
			'mcbConfig',
			array(
				'breakpointDesktop' => 1024,
				'i18n'              => array(
					'openMenu'  => __( 'Open menu', 'mcb' ),
					'closeMenu' => __( 'Close menu', 'mcb' ),
				),
			)
		);
	},
	20 // After Brikk's enqueues.
);

/**
 * Preload self-hosted display/body fonts (only the weights above the fold).
 */
add_action(
	'wp_head',
	function () {
		$fonts = array(
			'/assets/fonts/marcellus-latin-400.woff2',
			'/assets/fonts/inter-latin-var.woff2',
		);

		foreach ( $fonts as $font ) {
			if ( file_exists( MCB_DIR . $font ) ) {
				printf(
					'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
					esc_url( MCB_URI . $font )
				);
			}
		}
	},
	5
);

/**
 * Block editor: load the design tokens so the editor preview matches.
 */
add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_enqueue_style( 'mcb-editor', MCB_URI . '/assets/css/main.css', array(), mcb_asset_version( 'assets/css/main.css' ) );
	}
);
