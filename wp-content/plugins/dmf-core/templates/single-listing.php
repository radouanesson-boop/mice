<?php
/**
 * Single listing — framework fallback template.
 *
 * Themes override via {theme}/dmf/single-listing.php.
 *
 * @package DMF
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$dmf_id     = get_the_ID();
	$dmf_type   = dmf()->module( 'listings' )->registry()->get( get_post_type() );
	$dmf_geo    = dmf_get_field( 'geo', $dmf_id );
	$dmf_rating = get_post_meta( $dmf_id, '_dmf_rating', true );
	?>
	<main id="dmf-content" class="dmf-single dmf-single--<?php echo esc_attr( $dmf_type->kind ?? 'generic' ); ?>">

		<nav class="dmf-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'dmf' ); ?>">
			<?php foreach ( dmf_breadcrumbs() as $dmf_i => $dmf_crumb ) : ?>
				<?php if ( $dmf_i > 0 ) : ?><span aria-hidden="true">/</span><?php endif; ?>
				<?php if ( $dmf_crumb['url'] && ! is_singular() || $dmf_i < count( dmf_breadcrumbs() ) - 1 ) : ?>
					<a href="<?php echo esc_url( $dmf_crumb['url'] ); ?>"><?php echo esc_html( $dmf_crumb['name'] ); ?></a>
				<?php else : ?>
					<span aria-current="page"><?php echo esc_html( $dmf_crumb['name'] ); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>

		<header class="dmf-single__header">
			<h1 class="dmf-single__title"><?php the_title(); ?></h1>
			<?php if ( $dmf_rating ) : ?>
				<p class="dmf-single__rating">
					<?php
					printf(
						/* translators: 1: rating, 2: count. */
						esc_html__( 'Rated %1$s/5 (%2$s reviews)', 'dmf' ),
						esc_html( $dmf_rating ),
						esc_html( get_post_meta( $dmf_id, '_dmf_rating_count', true ) )
					);
					?>
				</p>
			<?php endif; ?>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="dmf-single__hero"><?php the_post_thumbnail( 'large', array( 'fetchpriority' => 'high' ) ); ?></figure>
		<?php endif; ?>

		<div class="dmf-single__layout">
			<div class="dmf-single__content">
				<?php the_content(); ?>

				<?php foreach ( ( $dmf_type->fields ?? array() ) as $dmf_group ) : ?>
					<?php
					$dmf_rows = array();
					foreach ( (array) ( $dmf_group['fields'] ?? array() ) as $dmf_config ) {
						$dmf_value = dmf_get_field( $dmf_config['key'], $dmf_id );
						if ( '' !== $dmf_value && array() !== $dmf_value && null !== $dmf_value && 'geo' !== $dmf_config['type'] && 'gallery' !== $dmf_config['type'] ) {
							$dmf_rows[ $dmf_config['label'] ] = is_array( $dmf_value ) ? implode( ', ', array_map( 'strval', $dmf_value ) ) : (string) $dmf_value;
						}
					}
					if ( ! $dmf_rows ) {
						continue;
					}
					?>
					<section class="dmf-single__group">
						<h2><?php echo esc_html( $dmf_group['label'] ?? '' ); ?></h2>
						<dl class="dmf-single__facts">
							<?php foreach ( $dmf_rows as $dmf_label => $dmf_value ) : ?>
								<div><dt><?php echo esc_html( $dmf_label ); ?></dt><dd><?php echo esc_html( $dmf_value ); ?></dd></div>
							<?php endforeach; ?>
						</dl>
					</section>
				<?php endforeach; ?>
			</div>

			<aside class="dmf-single__aside">
				<?php dmf_template( 'blocks/inquiry-form', array( 'listingId' => $dmf_id ) ); ?>

				<?php if ( is_array( $dmf_geo ) && '' !== ( $dmf_geo['lat'] ?? '' ) ) : ?>
					<div class="dmf-single__map" data-dmf-map
						data-lat="<?php echo esc_attr( $dmf_geo['lat'] ); ?>"
						data-lng="<?php echo esc_attr( $dmf_geo['lng'] ); ?>"></div>
				<?php endif; ?>
			</aside>
		</div>
	</main>
	<?php
endwhile;

get_footer();
