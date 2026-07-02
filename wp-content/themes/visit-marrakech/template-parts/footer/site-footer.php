<?php
/**
 * Site footer — board 1A: light stone, hairline top. Four columns
 * (brand + tagline / Explore / Plan / Newsletter) and a quiet legal bar.
 *
 * Entirely widget/menu driven — the design's column content maps to the
 * MCB Footer widget areas; menus are the no-widget fallback.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="mcb-footer">

	<?php if ( is_active_sidebar( 'vm-prefooter' ) ) : ?>
		<div class="mcb-footer__prefooter">
			<div class="mcb-container">
				<?php dynamic_sidebar( 'vm-prefooter' ); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="mcb-footer__main">
		<div class="mcb-container mcb-footer__grid">

			<div class="mcb-footer__col mcb-footer__col--brand">
				<?php
				if ( is_active_sidebar( 'vm-footer-brand' ) ) {
					dynamic_sidebar( 'vm-footer-brand' );
				} else {
					?>
					<div class="mcb-footer__brand-lockup">
						<?php mcb_icon( 'logo-mark' ); ?>
						<div>
							<p class="mcb-footer__brand-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
							<span class="mcb-footer__brand-tag"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></span>
						</div>
					</div>
					<p class="mcb-footer__brand-desc">
						<?php echo esc_html( get_theme_mod( 'vm_footer_tagline', __( 'The official Convention Bureau for Marrakech. Feel the Bahja Spirit.', 'vm' ) ) ); ?>
					</p>
					<?php
				}
				?>
			</div>

			<?php
			foreach ( array( 1, 2, 3 ) as $mcb_col ) :
				$mcb_sidebar = 'vm-footer-' . $mcb_col;
				$mcb_menu    = 'vm_footer_' . $mcb_col;

				if ( ! is_active_sidebar( $mcb_sidebar ) && ! has_nav_menu( $mcb_menu ) ) {
					continue;
				}
				?>
				<div class="mcb-footer__col">
					<?php
					if ( is_active_sidebar( $mcb_sidebar ) ) {
						dynamic_sidebar( $mcb_sidebar );
					} else {
						$mcb_menu_obj = wp_get_nav_menu_object( get_nav_menu_locations()[ $mcb_menu ] ?? 0 );

						if ( $mcb_menu_obj ) {
							echo '<h2 class="mcb-footer__widget-title">' . esc_html( $mcb_menu_obj->name ) . '</h2>';
						}

						wp_nav_menu(
							array(
								'theme_location' => $mcb_menu,
								'container'      => false,
								'menu_class'     => 'mcb-footer__menu',
								'depth'          => 1,
								'fallback_cb'    => false,
							)
						);
					}
					?>
				</div>
			<?php endforeach; ?>

		</div>

		<div class="mcb-container">
			<div class="mcb-footer__bottom">
				<div class="mcb-footer__bottom-inner">
					<p class="mcb-footer__copyright">
						<?php
						printf(
							/* translators: 1: year, 2: site name. */
							esc_html__( '© %1$s %2$s. All rights reserved.', 'vm' ),
							esc_html( gmdate( 'Y' ) ),
							esc_html( get_bloginfo( 'name' ) )
						);
						?>
					</p>

					<?php if ( has_nav_menu( 'vm_footer_legal' ) ) : ?>
						<nav class="mcb-footer__legal" aria-label="<?php esc_attr_e( 'Legal', 'vm' ); ?>">
							<?php
							wp_nav_menu(
								array(
									'theme_location' => 'vm_footer_legal',
									'container'      => false,
									'menu_class'     => 'mcb-footer__legal-menu',
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
