<?php
/**
 * Trust strip — board 1A: stone band under the hero listing reference
 * events, separated by small diamonds.
 *
 * Data: `dmf/trust_items` filter (array of strings) so the client's team
 * can manage it from a small config or an Elementor text widget instead.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$dmf_items = apply_filters(
	'dmf/trust_items',
	array( 'COP22', 'IMF · World Bank ’23', 'GITEX Africa', 'PURE Life Experiences', 'Marrakech Film Festival' )
);

if ( empty( $dmf_items ) ) {
	return;
}

$dmf_label = apply_filters( 'dmf/trust_label', __( 'Trusted to host the world’s leading events', 'dmf' ) );
?>
<div class="dmf-trust">
	<div class="dmf-container dmf-trust__inner">
		<p class="dmf-trust__label"><?php echo esc_html( $dmf_label ); ?></p>
		<div class="dmf-trust__row">
			<?php foreach ( $dmf_items as $dmf_i => $dmf_item ) : ?>
				<?php if ( $dmf_i > 0 ) : ?>
					<span class="dmf-trust__sep" aria-hidden="true">◆</span>
				<?php endif; ?>
				<span><?php echo esc_html( $dmf_item ); ?></span>
			<?php endforeach; ?>
		</div>
	</div>
</div>
