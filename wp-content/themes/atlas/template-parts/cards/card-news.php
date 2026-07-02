<?php
/**
 * Insight/news card — board 1A: borderless. Image (13px radius), sage
 * category label, title, read time.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$dmf_id       = (int) ( $args['post_id'] ?? get_the_ID() );
$dmf_category = get_the_category( $dmf_id );

// Estimate read time from content length (≈220 wpm), fine for a card meta.
$dmf_minutes = max( 1, (int) round( str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $dmf_id ) ) ) / 220 ) );
?>
<article class="dmf-card dmf-card--news">

	<a class="dmf-card__media dmf-media" href="<?php echo esc_url( get_permalink( $dmf_id ) ); ?>" tabindex="-1" aria-hidden="true">
		<?php dmf_thumbnail( $dmf_id, 'dmf-card-wide' ); ?>
	</a>

	<div class="dmf-card__body">
		<?php if ( ! empty( $dmf_category ) ) : ?>
			<p class="dmf-card__eyebrow"><?php echo esc_html( $dmf_category[0]->name ); ?></p>
		<?php endif; ?>

		<h3 class="dmf-card__title">
			<a href="<?php echo esc_url( get_permalink( $dmf_id ) ); ?>"><?php echo esc_html( get_the_title( $dmf_id ) ); ?></a>
		</h3>

		<div class="dmf-card__footer">
			<span class="dmf-card__time">
				<?php
				/* translators: %d: estimated minutes. */
				printf( esc_html__( '%d min read', 'dmf' ), (int) $dmf_minutes );
				?>
			</span>
		</div>
	</div>

</article>
