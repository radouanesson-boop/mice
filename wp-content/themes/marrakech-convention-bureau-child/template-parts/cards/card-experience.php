<?php
/**
 * Experience card — board 1A: full-image overlay card (344px), bottom
 * gradient, on-dark category label + name. Structure is self-contained
 * because the image fills the card.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_id   = (int) ( $args['post_id'] ?? get_the_ID() );
$mcb_term = mcb_listing_primary_term( $mcb_id );
?>
<article class="mcb-card mcb-card--experience">

	<span class="mcb-card__media mcb-media" aria-hidden="true">
		<?php mcb_thumbnail( $mcb_id, 'mcb-card-tall' ); ?>
	</span>

	<div class="mcb-card__body">
		<?php if ( $mcb_term ) : ?>
			<p class="mcb-card__eyebrow"><?php echo esc_html( $mcb_term->name ); ?></p>
		<?php endif; ?>

		<h3 class="mcb-card__title">
			<a href="<?php echo esc_url( get_permalink( $mcb_id ) ); ?>"><?php echo esc_html( get_the_title( $mcb_id ) ); ?></a>
		</h3>
	</div>

</article>
