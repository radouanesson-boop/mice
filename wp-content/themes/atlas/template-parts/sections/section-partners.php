<?php
/**
 * Partners strip — board 1A: single quiet row. Label left, partner names
 * (or grayscale logos) spread across the row.
 *
 *     add_filter( 'dmf/partners', fn() => [
 *         [ 'name' => 'Royal Mansour' ],
 *         [ 'name' => 'Four Seasons', 'logo_id' => 42, 'url' => 'https://…' ],
 *     ] );
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$dmf_partners = apply_filters( 'dmf/partners', array() );

if ( empty( $dmf_partners ) ) {
	return;
}

$dmf_label = apply_filters( 'dmf/partners_label', __( 'Our hospitality partners', 'dmf' ) );
?>
<div class="dmf-partners">
	<div class="dmf-container dmf-partners__inner">
		<p class="dmf-partners__label"><?php echo esc_html( $dmf_label ); ?></p>

		<ul class="dmf-partners__row">
			<?php foreach ( $dmf_partners as $dmf_partner ) : ?>
				<li>
					<?php if ( ! empty( $dmf_partner['url'] ) ) : ?>
						<a href="<?php echo esc_url( $dmf_partner['url'] ); ?>" rel="noopener" target="_blank" aria-label="<?php echo esc_attr( $dmf_partner['name'] ?? '' ); ?>">
					<?php endif; ?>

					<?php
					if ( ! empty( $dmf_partner['logo_id'] ) ) {
						echo wp_get_attachment_image( (int) $dmf_partner['logo_id'], 'dmf-logo', false, array( 'class' => 'dmf-partners__logo', 'loading' => 'lazy' ) );
					} elseif ( ! empty( $dmf_partner['name'] ) ) {
						echo '<span class="dmf-partners__name">' . esc_html( $dmf_partner['name'] ) . '</span>';
					}
					?>

					<?php if ( ! empty( $dmf_partner['url'] ) ) : ?>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
