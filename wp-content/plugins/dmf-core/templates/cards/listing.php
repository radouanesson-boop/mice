<?php
/**
 * Listing card — the framework's default card markup.
 *
 * Override in {theme}/dmf/cards/listing.php to reskin. The Visit Marrakech
 * theme does exactly that (design board 1A card language).
 *
 * @var array $args { post_id: int }
 * @package DMF
 */

defined( 'ABSPATH' ) || exit;

$dmf_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$dmf_kind   = dmf_listing_kind( $dmf_id );
$dmf_fields = dmf_get_card_fields( $dmf_id );
$dmf_terms  = get_the_terms( $dmf_id, 'dmf_category' );
?>
<article class="dmf-card dmf-card--<?php echo esc_attr( $dmf_kind ); ?>">
	<a class="dmf-card__media" href="<?php echo esc_url( get_permalink( $dmf_id ) ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail( $dmf_id ) ) : ?>
			<?php echo get_the_post_thumbnail( $dmf_id, 'medium_large', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
		<?php endif; ?>
		<?php if ( is_array( $dmf_terms ) && $dmf_terms ) : ?>
			<span class="dmf-card__badge"><?php echo esc_html( $dmf_terms[0]->name ); ?></span>
		<?php endif; ?>
	</a>

	<div class="dmf-card__body">
		<h3 class="dmf-card__title">
			<a href="<?php echo esc_url( get_permalink( $dmf_id ) ); ?>"><?php echo esc_html( get_the_title( $dmf_id ) ); ?></a>
		</h3>

		<?php if ( has_excerpt( $dmf_id ) ) : ?>
			<p class="dmf-card__excerpt"><?php echo esc_html( get_the_excerpt( $dmf_id ) ); ?></p>
		<?php endif; ?>

		<?php if ( $dmf_fields ) : ?>
			<ul class="dmf-card__meta">
				<?php foreach ( $dmf_fields as $dmf_field ) : ?>
					<li><?php echo esc_html( $dmf_field['value'] ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</article>
