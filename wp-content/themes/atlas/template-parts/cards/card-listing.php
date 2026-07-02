<?php
/**
 * Base listing card — design board 1A venue card: image with frosted tag
 * pill, title (Jost 500), sage subtitle, hairline-separated meta row.
 *
 * Kind-specific cards (card-venue, card-hotel, …) compose this part with a
 * modifier and their own meta rows, so the card skeleton exists once.
 *
 * @param array $args {
 *     @type int    $post_id  Listing ID (defaults to current post).
 *     @type string $kind     venue|hotel|event|listing.
 *     @type string $size     Image size (default dmf-card).
 *     @type string $subtitle Sage sub-line under the title.
 *     @type array  $meta     Rows: [ [ 'icon' => '…', 'text' => '…' ], … ].
 *     @type string $footer   Extra footer HTML (already escaped by caller).
 * }
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'post_id'  => get_the_ID(),
		'kind'     => 'listing',
		'size'     => 'dmf-card',
		'subtitle' => '',
		'meta'     => array(),
		'footer'   => '',
	)
);

$dmf_id     = (int) $args['post_id'];
$dmf_term   = dmf_listing_primary_term( $dmf_id );
$dmf_rating = dmf_listing_rating( $dmf_id );
?>
<article class="<?php echo dmf_bem( 'dmf-card', array( $args['kind'] ) ); ?>">

	<a class="dmf-card__media dmf-media" href="<?php echo esc_url( get_permalink( $dmf_id ) ); ?>" tabindex="-1" aria-hidden="true">
		<?php dmf_thumbnail( $dmf_id, $args['size'] ); ?>

		<?php if ( $dmf_term ) : ?>
			<span class="dmf-card__badge dmf-badge"><?php echo esc_html( $dmf_term->name ); ?></span>
		<?php endif; ?>
	</a>

	<div class="dmf-card__body">
		<div class="dmf-card__top">
			<h3 class="dmf-card__title">
				<a href="<?php echo esc_url( get_permalink( $dmf_id ) ); ?>"><?php echo esc_html( get_the_title( $dmf_id ) ); ?></a>
			</h3>

			<?php dmf_part( 'components/rating', array( 'rating' => $dmf_rating ) ); ?>
		</div>

		<?php if ( $args['subtitle'] ) : ?>
			<p class="dmf-card__subtitle"><?php echo esc_html( $args['subtitle'] ); ?></p>
		<?php endif; ?>

		<?php if ( has_excerpt( $dmf_id ) ) : ?>
			<p class="dmf-card__excerpt"><?php echo esc_html( get_the_excerpt( $dmf_id ) ); ?></p>
		<?php endif; ?>

		<?php
		$dmf_meta = array_filter( (array) $args['meta'], static fn( $row ) => ! empty( $row['text'] ) );
		if ( ! empty( $dmf_meta ) ) :
			?>
			<ul class="dmf-card__meta">
				<?php foreach ( $dmf_meta as $dmf_row ) : ?>
					<li class="dmf-card__meta-row">
						<?php if ( ! empty( $dmf_row['icon'] ) ) : ?>
							<?php dmf_icon( $dmf_row['icon'] ); ?>
						<?php endif; ?>
						<span><?php echo esc_html( $dmf_row['text'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $args['footer'] ) : ?>
			<div class="dmf-card__footer">
				<?php echo $args['footer']; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by caller. ?>
			</div>
		<?php endif; ?>
	</div>

</article>
