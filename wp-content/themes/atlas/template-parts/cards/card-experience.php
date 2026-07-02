<?php
/**
 * Experience card — board 1A: full-image overlay card (344px), bottom
 * gradient, on-dark category label + name. Structure is self-contained
 * because the image fills the card.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$dmf_id   = (int) ( $args['post_id'] ?? get_the_ID() );
$dmf_term = dmf_listing_primary_term( $dmf_id );
?>
<article class="dmf-card dmf-card--experience">

	<span class="dmf-card__media dmf-media" aria-hidden="true">
		<?php dmf_thumbnail( $dmf_id, 'dmf-card-tall' ); ?>
	</span>

	<div class="dmf-card__body">
		<?php if ( $dmf_term ) : ?>
			<p class="dmf-card__eyebrow"><?php echo esc_html( $dmf_term->name ); ?></p>
		<?php endif; ?>

		<h3 class="dmf-card__title">
			<a href="<?php echo esc_url( get_permalink( $dmf_id ) ); ?>"><?php echo esc_html( get_the_title( $dmf_id ) ); ?></a>
		</h3>
	</div>

</article>
