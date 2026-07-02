<?php
/**
 * Site footer — board 1A: light stone, hairline top. Four columns
 * (brand + tagline / Explore / Plan / Newsletter) and a quiet legal bar.
 *
 * Entirely widget/menu driven — the design's column content maps to the
 * Atlas footer widget areas; menus are the no-widget fallback.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="dmf-footer">

	<?php if ( is_active_sidebar( 'dmf-prefooter' ) ) : ?>
		<div class="dmf-footer__prefooter">
			<div class="dmf-container">
				<?php dynamic_sidebar( 'dmf-prefooter' ); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="dmf-footer__main">
		<div class="dmf-container dmf-footer__grid">

			<div class="dmf-footer__col dmf-footer__col--brand">
				<?php
				if ( is_active_sidebar( 'dmf-footer-brand' ) ) {
					dynamic_sidebar( 'dmf-footer-brand' );
				} else {
					?>
					<div class="dmf-footer__brand-lockup">
						<?php dmf_icon( 'logo-mark' ); ?>
						<div>
							<p class="dmf-footer__brand-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
							<span class="dmf-footer__brand-tag"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></span>
						</div>
					</div>
					<p class="dmf-footer__brand-desc">
						<?php echo esc_html( get_theme_mod( 'dmf_footer_tagline', __( 'The official Convention Bureau for Marrakech. Feel the Bahja Spirit.', 'dmf' ) ) ); ?>
					</p>
					<?php
				}
				?>
			</div>

			<?php
			foreach ( array( 1, 2, 3 ) as $dmf_col ) :
				$dmf_sidebar = 'dmf-footer-' . $dmf_col;
				$dmf_menu    = 'dmf_footer_' . $dmf_col;

				if ( ! is_active_sidebar( $dmf_sidebar ) && ! has_nav_menu( $dmf_menu ) ) {
					continue;
				}
				?>
				<div class="dmf-footer__col">
					<?php
					if ( is_active_sidebar( $dmf_sidebar ) ) {
						dynamic_sidebar( $dmf_sidebar );
					} else {
						$dmf_menu_obj = wp_get_nav_menu_object( get_nav_menu_locations()[ $dmf_menu ] ?? 0 );

						if ( $dmf_menu_obj ) {
							echo '<h2 class="dmf-footer__widget-title">' . esc_html( $dmf_menu_obj->name ) . '</h2>';
						}

						wp_nav_menu(
							array(
								'theme_location' => $dmf_menu,
								'container'      => false,
								'menu_class'     => 'dmf-footer__menu',
								'depth'          => 1,
								'fallback_cb'    => false,
							)
						);
					}
					?>
				</div>
			<?php endforeach; ?>

		</div>

		<div class="dmf-container">
			<div class="dmf-footer__bottom">
				<div class="dmf-footer__bottom-inner">
					<p class="dmf-footer__copyright">
						<?php
						printf(
							/* translators: 1: year, 2: site name. */
							esc_html__( '© %1$s %2$s. All rights reserved.', 'dmf' ),
							esc_html( gmdate( 'Y' ) ),
							esc_html( get_bloginfo( 'name' ) )
						);
						?>
					</p>

					<?php if ( has_nav_menu( 'dmf_footer_legal' ) ) : ?>
						<nav class="dmf-footer__legal" aria-label="<?php esc_attr_e( 'Legal', 'dmf' ); ?>">
							<?php
							wp_nav_menu(
								array(
									'theme_location' => 'dmf_footer_legal',
									'container'      => false,
									'menu_class'     => 'dmf-footer__legal-menu',
									'depth'          => 1,
									'fallback_cb'    => false,
								)
							);
							?>
						</nav>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

</footer>
