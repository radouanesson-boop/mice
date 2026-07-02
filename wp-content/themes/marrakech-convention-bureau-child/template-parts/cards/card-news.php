<?php
/**
 * Insight/news card — board 1A: borderless. Image (13px radius), sage
 * category label, title, read time.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_id       = (int) ( $args['post_id'] ?? get_the_ID() );
$mcb_category = get_the_category( $mcb_id );

// Estimate read time from content length (≈220 wpm), fine for a card meta.
$mcb_minutes = max( 1, (int) round( str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $mcb_id ) ) ) / 220 ) );
?>
<article class="mcb-card mcb-card--news">

	<a class="mcb-card__media mcb-media" href="<?php echo esc_url( get_permalink( $mcb_id ) ); ?>" tabindex="-1" aria-hidden="true">
		<?php mcb_thumbnail( $mcb_id, 'mcb-card-wide' ); ?>
	</a>

	<div class="mcb-card__body">
		<?php if ( ! empty( $mcb_category ) ) : ?>
			<p class="mcb-card__eyebrow"><?php echo esc_html( $mcb_category[0]->name ); ?></p>
		<?php endif; ?>

		<h3 class="mcb-card__title">
			<a href="<?php echo esc_url( get_permalink( $mcb_id ) ); ?>"><?php echo esc_html( get_the_title( $mcb_id ) ); ?></a>
		</h3>

		<div class="mcb-card__footer">
			<span class="mcb-card__time">
				<?php
				/* translators: %d: estimated minutes. */
				printf( esc_html__( '%d min read', 'mcb' ), (int) $mcb_minutes );
				?>
			</span>
		</div>
	</div>

</article>
