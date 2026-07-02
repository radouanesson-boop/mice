<?php
/**
 * Site footer: pre-footer CTA strip, 4 widget columns, legal bar.
 *
 * Entirely widget/menu driven — no hardcoded content.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="mcb-footer">

	<?php if ( is_active_sidebar( 'mcb-prefooter' ) ) : ?>
		<div class="mcb-footer__prefooter">
			<div class="mcb-container">
				<?php dynamic_sidebar( 'mcb-prefooter' ); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="mcb-footer__main">
		<div class="mcb-container mcb-footer__grid">

			<div class="mcb-footer__col mcb-footer__col--brand">
				<?php
				if ( is_active_sidebar( 'mcb-footer-brand' ) ) {
					dynamic_sidebar( 'mcb-footer-brand' );
				} else {
					?>
					<p class="mcb-footer__brand-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
					<p class="mcb-footer__brand-desc"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
					<?php
				}
				?>
			</div>

			<?php
			foreach ( array( 1, 2, 3 ) as $mcb_col ) :
				$mcb_sidebar = 'mcb-footer-' . $mcb_col;
				$mcb_menu    = 'mcb_footer_' . $mcb_col;

				if ( ! is_active_sidebar( $mcb_sidebar ) && ! has_nav_menu( $mcb_menu ) ) {
					continue;
				}
				?>
				<div class="mcb-footer__col">
					<?php
					if ( is_active_sidebar( $mcb_sidebar ) ) {
						dynamic_sidebar( $mcb_sidebar );
					} else {
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
	</div>

	<div class="mcb-footer__bottom">
		<div class="mcb-container mcb-footer__bottom-inner">
			<p class="mcb-footer__copyright">
				<?php
				printf(
					/* translators: 1: year, 2: site name. */
					esc_html__( '© %1$s %2$s. All rights reserved.', 'mcb' ),
					esc_html( gmdate( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</p>

			<?php if ( has_nav_menu( 'mcb_footer_legal' ) ) : ?>
				<nav class="mcb-footer__legal" aria-label="<?php esc_attr_e( 'Legal', 'mcb' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'mcb_footer_legal',
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

</footer>
