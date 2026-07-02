<?php
/**
 * Mobile navigation — design board 1C: full-screen deep-green takeover,
 * large light links with sage arrows, EN/FR + white RFP button at the
 * bottom. Sub-menus expand in place (JS in assets/js/main.js).
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$dmf_location = has_nav_menu( 'dmf_mobile' ) ? 'dmf_mobile' : 'dmf_primary';

$dmf_cta = apply_filters(
	'dmf/header_cta',
	array(
		'text' => get_theme_mod( 'dmf_header_cta_text', __( 'Submit an RFP', 'dmf' ) ),
		'url'  => get_theme_mod( 'dmf_header_cta_url', home_url( '/submit-rfp/' ) ),
	)
);
?>
<div class="dmf-mobile-nav" id="dmf-mobile-nav" data-dmf-mobile-nav hidden>
	<div class="dmf-mobile-nav__scrim" data-dmf-nav-close></div>

	<div class="dmf-mobile-nav__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Site menu', 'dmf' ); ?>">
		<div class="dmf-mobile-nav__head">
			<span class="dmf-mobile-nav__brand"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
			<button class="dmf-mobile-nav__close" type="button" data-dmf-nav-close aria-label="<?php esc_attr_e( 'Close menu', 'dmf' ); ?>">
				<?php dmf_icon( 'close' ); ?>
			</button>
		</div>

		<nav class="dmf-mobile-nav__nav" aria-label="<?php esc_attr_e( 'Mobile', 'dmf' ); ?>">
			<?php
			if ( has_nav_menu( $dmf_location ) ) {
				wp_nav_menu(
					array(
						'theme_location' => $dmf_location,
						'container'      => false,
						'menu_class'     => 'dmf-mobile-nav__menu',
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
			}
			?>
		</nav>

		<div class="dmf-mobile-nav__footer">
			<?php if ( function_exists( 'pll_the_languages' ) ) : ?>
				<ul class="dmf-mobile-nav__lang">
					<?php pll_the_languages( array( 'display_names_as' => 'slug', 'hide_if_no_translation' => 0, 'echo' => 1 ) ); // phpcs:ignore ?>
				</ul>
			<?php endif; ?>

			<?php if ( ! empty( $dmf_cta['text'] ) ) : ?>
				<a class="dmf-btn dmf-btn--block" href="<?php echo esc_url( $dmf_cta['url'] ); ?>">
					<?php echo esc_html( $dmf_cta['text'] ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
