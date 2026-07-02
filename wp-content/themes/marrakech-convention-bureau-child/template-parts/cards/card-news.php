<?php
/**
 * News/blog card.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_id       = (int) ( $args['post_id'] ?? get_the_ID() );
$mcb_category = get_the_category( $mcb_id );
?>
<article class="mcb-card mcb-card--news">

	<a class="mcb-card__media mcb-media" href="<?php echo esc_url( get_permalink( $mcb_id ) ); ?>" tabindex="-1" aria-hidden="true">
		<?php mcb_thumbnail( $mcb_id, 'mcb-card-wide' ); ?>
	</a>

	<div class="mcb-card__body">
		<div class="mcb-card__eyebrow">
			<?php if ( ! empty( $mcb_category ) ) : ?>
				<span class="mcb-badge mcb-badge--soft"><?php echo esc_html( $mcb_category[0]->name ); ?></span>
			<?php endif; ?>
			<time class="mcb-card__time" datetime="<?php echo esc_attr( get_the_date( 'c', $mcb_id ) ); ?>">
				<?php echo esc_html( get_the_date( '', $mcb_id ) ); ?>
			</time>
		</div>

		<h3 class="mcb-card__title">
			<a href="<?php echo esc_url( get_permalink( $mcb_id ) ); ?>"><?php echo esc_html( get_the_title( $mcb_id ) ); ?></a>
		</h3>

		<p class="mcb-card__excerpt"><?php echo esc_html( get_the_excerpt( $mcb_id ) ); ?></p>

		<a class="mcb-card__link" href="<?php echo esc_url( get_permalink( $mcb_id ) ); ?>">
			<?php esc_html_e( 'Read more', 'mcb' ); ?>
			<?php mcb_icon( 'arrow-right' ); ?>
		</a>
	</div>

</article>
