<?php
/**
 * Mobile navigation — design board 1C: full-screen deep-green takeover,
 * large light links with sage arrows, EN/FR + white RFP button at the
 * bottom. Sub-menus expand in place (JS in assets/js/main.js).
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_location = has_nav_menu( 'mcb_mobile' ) ? 'mcb_mobile' : 'mcb_primary';

$mcb_cta = apply_filters(
	'mcb/header_cta',
	array(
		'text' => get_theme_mod( 'mcb_header_cta_text', __( 'Submit an RFP', 'mcb' ) ),
		'url'  => get_theme_mod( 'mcb_header_cta_url', home_url( '/submit-rfp/' ) ),
	)
);
?>
<div class="mcb-mobile-nav" id="mcb-mobile-nav" data-mcb-mobile-nav hidden>
	<div class="mcb-mobile-nav__scrim" data-mcb-nav-close></div>

	<div class="mcb-mobile-nav__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Site menu', 'mcb' ); ?>">
		<div class="mcb-mobile-nav__head">
			<span class="mcb-mobile-nav__brand"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
			<button class="mcb-mobile-nav__close" type="button" data-mcb-nav-close aria-label="<?php esc_attr_e( 'Close menu', 'mcb' ); ?>">
				<?php mcb_icon( 'close' ); ?>
			</button>
		</div>

		<nav class="mcb-mobile-nav__nav" aria-label="<?php esc_attr_e( 'Mobile', 'mcb' ); ?>">
			<?php
			if ( has_nav_menu( $mcb_location ) ) {
				wp_nav_menu(
					array(
						'theme_location' => $mcb_location,
						'container'      => false,
						'menu_class'     => 'mcb-mobile-nav__menu',
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
			}
			?>
		</nav>

		<div class="mcb-mobile-nav__footer">
			<?php if ( function_exists( 'pll_the_languages' ) ) : ?>
				<ul class="mcb-mobile-nav__lang">
					<?php pll_the_languages( array( 'display_names_as' => 'slug', 'hide_if_no_translation' => 0, 'echo' => 1 ) ); // phpcs:ignore ?>
				</ul>
			<?php endif; ?>

			<?php if ( ! empty( $mcb_cta['text'] ) ) : ?>
				<a class="mcb-btn mcb-btn--block" href="<?php echo esc_url( $mcb_cta['url'] ); ?>">
					<?php echo esc_html( $mcb_cta['text'] ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
