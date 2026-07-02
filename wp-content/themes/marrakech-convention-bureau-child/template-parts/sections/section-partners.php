<?php
/**
 * Partners strip — board 1A: single quiet row. Label left, partner names
 * (or grayscale logos) spread across the row.
 *
 *     add_filter( 'mcb/partners', fn() => [
 *         [ 'name' => 'Royal Mansour' ],
 *         [ 'name' => 'Four Seasons', 'logo_id' => 42, 'url' => 'https://…' ],
 *     ] );
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_partners = apply_filters( 'mcb/partners', array() );

if ( empty( $mcb_partners ) ) {
	return;
}

$mcb_label = apply_filters( 'mcb/partners_label', __( 'Our hospitality partners', 'mcb' ) );
?>
<div class="mcb-partners">
	<div class="mcb-container mcb-partners__inner">
		<p class="mcb-partners__label"><?php echo esc_html( $mcb_label ); ?></p>

		<ul class="mcb-partners__row">
			<?php foreach ( $mcb_partners as $mcb_partner ) : ?>
				<li>
					<?php if ( ! empty( $mcb_partner['url'] ) ) : ?>
						<a href="<?php echo esc_url( $mcb_partner['url'] ); ?>" rel="noopener" target="_blank" aria-label="<?php echo esc_attr( $mcb_partner['name'] ?? '' ); ?>">
					<?php endif; ?>

					<?php
					if ( ! empty( $mcb_partner['logo_id'] ) ) {
						echo wp_get_attachment_image( (int) $mcb_partner['logo_id'], 'mcb-logo', false, array( 'class' => 'mcb-partners__logo', 'loading' => 'lazy' ) );
					} elseif ( ! empty( $mcb_partner['name'] ) ) {
						echo '<span class="mcb-partners__name">' . esc_html( $mcb_partner['name'] ) . '</span>';
					}
					?>

					<?php if ( ! empty( $mcb_partner['url'] ) ) : ?>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
