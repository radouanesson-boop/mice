<?php
/**
 * Site header — design board 1A: white 84px bar, brand lockup (Bahja mark +
 * VISIT MARRAKECH / CONVENTION BUREAU), centered nav with mega menu,
 * EN/FR switcher, search, "Submit an RFP" pill.
 *
 * All content is editor-managed: logo via Customizer (custom-logo), menus
 * via Appearance → Menus, CTA via theme mods / `dmf/header_cta`, languages
 * via Polylang/WPML when installed.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$dmf_cta = apply_filters(
	'dmf/header_cta',
	array(
		'text' => get_theme_mod( 'dmf_header_cta_text', __( 'Submit an RFP', 'dmf' ) ),
		'url'  => get_theme_mod( 'dmf_header_cta_url', home_url( '/submit-rfp/' ) ),
	)
);
?>
<header class="dmf-header" data-dmf-header>

	<?php if ( has_nav_menu( 'dmf_topbar' ) ) : ?>
		<div class="dmf-topbar">
			<div class="dmf-container dmf-topbar__inner">
				<p class="dmf-topbar__tagline"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
				<nav class="dmf-topbar__nav" aria-label="<?php esc_attr_e( 'Quick links', 'dmf' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'dmf_topbar',
							'container'      => false,
							'menu_class'     => 'dmf-topbar__menu',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			</div>
		</div>
	<?php endif; ?>

	<div class="dmf-header__bar">
		<div class="dmf-container dmf-header__inner">

			<div class="dmf-header__brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<span class="dmf-header__brand-mark"><?php dmf_icon( 'logo-mark' ); ?></span>
						<span>
							<span class="dmf-header__brand-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
							<span class="dmf-header__brand-tag"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></span>
						</span>
					</a>
				<?php endif; ?>
			</div>

			<nav class="dmf-nav" id="dmf-primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'dmf' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'dmf_primary',
						'container'      => false,
						'menu_class'     => 'dmf-nav__menu',
						'depth'          => 3,
						'walker'         => new DMF_Mega_Menu_Walker(),
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>

			<div class="dmf-header__actions">

				<?php if ( function_exists( 'pll_the_languages' ) ) : ?>
					<ul class="dmf-header__lang">
						<?php pll_the_languages( array( 'display_names_as' => 'slug', 'hide_if_no_translation' => 0, 'echo' => 1, 'raw' => 0 ) ); // phpcs:ignore ?>
					</ul>
				<?php elseif ( has_action( 'wpml_add_language_selector' ) ) : ?>
					<div class="dmf-header__lang"><?php do_action( 'wpml_add_language_selector' ); ?></div>
				<?php endif; ?>

				<a class="dmf-header__search" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( 'Search', 'dmf' ); ?>">
					<?php dmf_icon( 'search' ); ?>
				</a>

				<?php if ( ! empty( $dmf_cta['text'] ) ) : ?>
					<a class="dmf-btn dmf-btn--primary dmf-btn--sm dmf-header__cta" href="<?php echo esc_url( $dmf_cta['url'] ); ?>">
						<?php echo esc_html( $dmf_cta['text'] ); ?>
					</a>
				<?php endif; ?>

				<button class="dmf-burger" type="button" data-dmf-nav-toggle
					aria-expanded="false" aria-controls="dmf-mobile-nav"
					aria-label="<?php esc_attr_e( 'Open menu', 'dmf' ); ?>">
					<span class="dmf-burger__line"></span>
					<span class="dmf-burger__line"></span>
					<span class="dmf-burger__line"></span>
				</button>
			</div>

		</div>
	</div>

	<?php dmf_part( 'header/nav-mobile' ); ?>

</header>
