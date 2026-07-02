<?php
/**
 * Partners / institutional logos.
 *
 * Logos come from a WP menu of custom links with images NOT hardcoded:
 * source order is `mcb/partners` filter (array of [name, logo_id, url])
 * → attachments tagged via theme mod → nothing.
 *
 * In Elementor: use an Image Carousel styled by .mcb-partners classes.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Provide partner logos:
 *
 *     add_filter( 'mcb/partners', fn() => [
 *         [ 'name' => 'GL events', 'logo_id' => 42, 'url' => 'https://…' ],
 *     ] );
 */
$mcb_partners = apply_filters( 'mcb/partners', array() );

if ( empty( $mcb_partners ) ) {
	return;
}
?>
<section class="mcb-section mcb-section--partners mcb-partners">
	<div class="mcb-container">

		<?php
		mcb_part(
			'components/section-heading',
			array(
				'kicker' => __( 'Partners', 'mcb' ),
				'title'  => __( 'Trusted by institutions & industry', 'mcb' ),
			)
		);
		?>

		<ul class="mcb-partners__row">
			<?php foreach ( $mcb_partners as $mcb_partner ) : ?>
				<li class="mcb-partners__item">
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
</section>
