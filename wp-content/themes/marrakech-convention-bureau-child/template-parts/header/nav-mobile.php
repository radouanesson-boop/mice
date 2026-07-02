<?php
/**
 * Mobile navigation drawer.
 *
 * Uses the dedicated `mcb_mobile` menu when assigned, otherwise falls back
 * to the primary menu. Sub-menus become tap-to-expand accordions (JS in
 * assets/js/main.js).
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_location = has_nav_menu( 'mcb_mobile' ) ? 'mcb_mobile' : 'mcb_primary';
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
			<?php
			$mcb_cta = apply_filters(
				'mcb/header_cta',
				array(
					'text' => get_theme_mod( 'mcb_header_cta_text', __( 'Plan your event', 'mcb' ) ),
					'url'  => get_theme_mod( 'mcb_header_cta_url', home_url( '/plan-your-event/' ) ),
				)
			);

			if ( ! empty( $mcb_cta['text'] ) ) :
				?>
				<a class="mcb-btn mcb-btn--primary mcb-btn--block" href="<?php echo esc_url( $mcb_cta['url'] ); ?>">
					<?php echo esc_html( $mcb_cta['text'] ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
