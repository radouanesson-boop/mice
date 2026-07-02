<?php
/**
 * Site header — design board 1A: white 84px bar, brand lockup (Bahja mark +
 * VISIT MARRAKECH / CONVENTION BUREAU), centered nav with mega menu,
 * EN/FR switcher, search, "Submit an RFP" pill.
 *
 * All content is editor-managed: logo via Customizer (custom-logo), menus
 * via Appearance → Menus, CTA via theme mods / `mcb/header_cta`, languages
 * via Polylang/WPML when installed.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_cta = apply_filters(
	'mcb/header_cta',
	array(
		'text' => get_theme_mod( 'mcb_header_cta_text', __( 'Submit an RFP', 'mcb' ) ),
		'url'  => get_theme_mod( 'mcb_header_cta_url', home_url( '/submit-rfp/' ) ),
	)
);
?>
<header class="mcb-header" data-mcb-header>

	<?php if ( has_nav_menu( 'mcb_topbar' ) ) : ?>
		<div class="mcb-topbar">
			<div class="mcb-container mcb-topbar__inner">
				<p class="mcb-topbar__tagline"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
				<nav class="mcb-topbar__nav" aria-label="<?php esc_attr_e( 'Quick links', 'mcb' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'mcb_topbar',
							'container'      => false,
							'menu_class'     => 'mcb-topbar__menu',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			</div>
		</div>
	<?php endif; ?>

	<div class="mcb-header__bar">
		<div class="mcb-container mcb-header__inner">

			<div class="mcb-header__brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<span class="mcb-header__brand-mark"><?php mcb_icon( 'logo-mark' ); ?></span>
						<span>
							<span class="mcb-header__brand-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
							<span class="mcb-header__brand-tag"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></span>
						</span>
					</a>
				<?php endif; ?>
			</div>

			<nav class="mcb-nav" id="mcb-primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'mcb' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'mcb_primary',
						'container'      => false,
						'menu_class'     => 'mcb-nav__menu',
						'depth'          => 3,
						'walker'         => new MCB_Mega_Menu_Walker(),
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>

			<div class="mcb-header__actions">

				<?php if ( function_exists( 'pll_the_languages' ) ) : ?>
					<ul class="mcb-header__lang">
						<?php pll_the_languages( array( 'display_names_as' => 'slug', 'hide_if_no_translation' => 0, 'echo' => 1, 'raw' => 0 ) ); // phpcs:ignore ?>
					</ul>
				<?php elseif ( has_action( 'wpml_add_language_selector' ) ) : ?>
					<div class="mcb-header__lang"><?php do_action( 'wpml_add_language_selector' ); ?></div>
				<?php endif; ?>

				<a class="mcb-header__search" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( 'Search', 'mcb' ); ?>">
					<?php mcb_icon( 'search' ); ?>
				</a>

				<?php if ( ! empty( $mcb_cta['text'] ) ) : ?>
					<a class="mcb-btn mcb-btn--primary mcb-btn--sm mcb-header__cta" href="<?php echo esc_url( $mcb_cta['url'] ); ?>">
						<?php echo esc_html( $mcb_cta['text'] ); ?>
					</a>
				<?php endif; ?>

				<button class="mcb-burger" type="button" data-mcb-nav-toggle
					aria-expanded="false" aria-controls="mcb-mobile-nav"
					aria-label="<?php esc_attr_e( 'Open menu', 'mcb' ); ?>">
					<span class="mcb-burger__line"></span>
					<span class="mcb-burger__line"></span>
					<span class="mcb-burger__line"></span>
				</button>
			</div>

		</div>
	</div>

	<?php mcb_part( 'header/nav-mobile' ); ?>

</header>
