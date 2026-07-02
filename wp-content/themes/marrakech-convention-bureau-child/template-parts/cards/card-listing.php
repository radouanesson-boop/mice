<?php
/**
 * Base listing card — shared markup for every card kind.
 *
 * Kind-specific cards (card-venue, card-hotel, …) compose this part with a
 * modifier and their own meta rows, so the card skeleton exists exactly
 * once (image → badge → title → meta → footer).
 *
 * @param array $args {
 *     @type int    $post_id   Listing ID (defaults to current post).
 *     @type string $kind      venue|hotel|experience|event|listing.
 *     @type string $size      Image size (default mcb-card).
 *     @type array  $meta      Rows: [ [ 'icon' => 'map-pin', 'text' => '…' ], … ].
 *     @type string $footer    Extra footer HTML (already escaped by caller).
 * }
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'post_id' => get_the_ID(),
		'kind'    => 'listing',
		'size'    => 'mcb-card',
		'meta'    => array(),
		'footer'  => '',
	)
);

$mcb_id     = (int) $args['post_id'];
$mcb_term   = mcb_listing_primary_term( $mcb_id );
$mcb_rating = mcb_listing_rating( $mcb_id );
?>
<article class="<?php echo mcb_bem( 'mcb-card', array( $args['kind'] ) ); ?>">

	<a class="mcb-card__media mcb-media" href="<?php echo esc_url( get_permalink( $mcb_id ) ); ?>" tabindex="-1" aria-hidden="true">
		<?php mcb_thumbnail( $mcb_id, $args['size'] ); ?>

		<?php if ( $mcb_term ) : ?>
			<span class="mcb-card__badge mcb-badge"><?php echo esc_html( $mcb_term->name ); ?></span>
		<?php endif; ?>
	</a>

	<div class="mcb-card__body">
		<div class="mcb-card__top">
			<h3 class="mcb-card__title">
				<a href="<?php echo esc_url( get_permalink( $mcb_id ) ); ?>"><?php echo esc_html( get_the_title( $mcb_id ) ); ?></a>
			</h3>

			<?php mcb_part( 'components/rating', array( 'rating' => $mcb_rating ) ); ?>
		</div>

		<?php if ( ! empty( $args['meta'] ) ) : ?>
			<ul class="mcb-card__meta">
				<?php foreach ( $args['meta'] as $mcb_row ) : ?>
					<?php
					if ( empty( $mcb_row['text'] ) ) {
						continue;
					}
					?>
					<li class="mcb-card__meta-row">
						<?php mcb_icon( $mcb_row['icon'] ?? 'info' ); ?>
						<span><?php echo esc_html( $mcb_row['text'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( has_excerpt( $mcb_id ) ) : ?>
			<p class="mcb-card__excerpt"><?php echo esc_html( get_the_excerpt( $mcb_id ) ); ?></p>
		<?php endif; ?>

		<div class="mcb-card__footer">
			<?php echo $args['footer']; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by caller. ?>

			<a class="mcb-card__link" href="<?php echo esc_url( get_permalink( $mcb_id ) ); ?>">
				<?php esc_html_e( 'View details', 'mcb' ); ?>
				<?php mcb_icon( 'arrow-right' ); ?>
			</a>
		</div>
	</div>

</article>
