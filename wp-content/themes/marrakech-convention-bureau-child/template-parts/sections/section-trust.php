<?php
/**
 * Trust strip — board 1A: stone band under the hero listing reference
 * events, separated by small diamonds.
 *
 * Data: `mcb/trust_items` filter (array of strings) so the client's team
 * can manage it from a small config or an Elementor text widget instead.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_items = apply_filters(
	'mcb/trust_items',
	array( 'COP22', 'IMF · World Bank ’23', 'GITEX Africa', 'PURE Life Experiences', 'Marrakech Film Festival' )
);

if ( empty( $mcb_items ) ) {
	return;
}

$mcb_label = apply_filters( 'mcb/trust_label', __( 'Trusted to host the world’s leading events', 'mcb' ) );
?>
<div class="mcb-trust">
	<div class="mcb-container mcb-trust__inner">
		<p class="mcb-trust__label"><?php echo esc_html( $mcb_label ); ?></p>
		<div class="mcb-trust__row">
			<?php foreach ( $mcb_items as $mcb_i => $mcb_item ) : ?>
				<?php if ( $mcb_i > 0 ) : ?>
					<span class="mcb-trust__sep" aria-hidden="true">◆</span>
				<?php endif; ?>
				<span><?php echo esc_html( $mcb_item ); ?></span>
			<?php endforeach; ?>
		</div>
	</div>
</div>
