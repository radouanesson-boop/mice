<?php
/**
 * DMF override — single listing in the Visit Marrakech design:
 * breadcrumbs, display title, gallery hero, fact groups, sticky RFP aside.
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$vm_id   = get_the_ID();
	$vm_type = dmf()->module( 'listings' )->registry()->get( get_post_type() );
	$vm_geo  = dmf_get_field( 'geo', $vm_id );
	?>
	<div class="mcb-page-hero">
		<div class="mcb-container">
			<?php mcb_breadcrumbs(); ?>
			<h1 class="mcb-page-hero__title"><?php the_title(); ?></h1>
			<?php $vm_operator = dmf_get_field( 'operator', $vm_id ); ?>
			<?php if ( $vm_operator ) : ?>
				<p class="mcb-card__subtitle"><?php echo esc_html( $vm_operator ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<article <?php post_class( 'mcb-section mcb-listing-single' ); ?>>
		<div class="mcb-container">

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="mcb-listing-single__hero">
					<?php the_post_thumbnail( 'mcb-hero', array( 'fetchpriority' => 'high' ) ); ?>
				</figure>
			<?php endif; ?>

			<div class="mcb-listing-single__layout">
				<div class="mcb-listing-single__content mcb-prose">
					<?php the_content(); ?>

					<?php foreach ( ( $vm_type->fields ?? array() ) as $vm_group ) : ?>
						<?php
						$vm_rows = array();
						foreach ( (array) ( $vm_group['fields'] ?? array() ) as $vm_config ) {
							if ( in_array( $vm_config['type'] ?? 'text', array( 'geo', 'gallery', 'file' ), true ) ) {
								continue;
							}
							$vm_value = dmf_get_field( $vm_config['key'], $vm_id );
							if ( '' !== $vm_value && array() !== $vm_value && null !== $vm_value ) {
								$vm_rows[ $vm_config['label'] ] = is_array( $vm_value ) ? implode( ', ', array_map( 'strval', $vm_value ) ) : (string) $vm_value;
							}
						}
						if ( ! $vm_rows ) {
							continue;
						}
						?>
						<section class="mcb-listing-single__group">
							<h2><?php echo esc_html( $vm_group['label'] ?? '' ); ?></h2>
							<dl class="mcb-facts">
								<?php foreach ( $vm_rows as $vm_label => $vm_value ) : ?>
									<div class="mcb-facts__row">
										<dt><?php echo esc_html( $vm_label ); ?></dt>
										<dd><?php echo esc_html( $vm_value ); ?></dd>
									</div>
								<?php endforeach; ?>
							</dl>
						</section>
					<?php endforeach; ?>
				</div>

				<aside class="mcb-listing-single__aside">
					<div class="mcb-aside-card">
						<?php dmf_template( 'blocks/inquiry-form', array( 'listingId' => $vm_id ) ); ?>
					</div>

					<?php if ( is_array( $vm_geo ) && '' !== ( $vm_geo['lat'] ?? '' ) ) : ?>
						<div class="mcb-aside-card mcb-aside-card--map" data-dmf-map
							data-lat="<?php echo esc_attr( $vm_geo['lat'] ); ?>"
							data-lng="<?php echo esc_attr( $vm_geo['lng'] ); ?>"></div>
					<?php endif; ?>
				</aside>
			</div>

		</div>
	</article>
	<?php
endwhile;

get_footer();
