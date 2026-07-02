<?php
/**
 * Block: dmf/events-list — upcoming events.
 *
 * @var array $args { count }
 * @package DMF
 */

defined( 'ABSPATH' ) || exit;

$dmf_query = new WP_Query(
	array(
		'post_type'      => 'dmf_event',
		'post_status'    => 'publish',
		'posts_per_page' => max( 1, (int) ( $args['count'] ?? 3 ) ),
		'no_found_rows'  => true,
		'meta_key'       => '_dmf_start_date',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'     => '_dmf_start_date',
				'value'   => gmdate( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
	)
);

if ( ! $dmf_query->have_posts() ) {
	return;
}
?>
<ul class="dmf-events">
	<?php
	while ( $dmf_query->have_posts() ) :
		$dmf_query->the_post();
		$dmf_start = dmf_get_field( 'start_date' );
		$dmf_stamp = $dmf_start ? strtotime( $dmf_start ) : 0;
		?>
		<li class="dmf-events__item">
			<?php if ( $dmf_stamp ) : ?>
				<time class="dmf-events__date" datetime="<?php echo esc_attr( gmdate( 'Y-m-d', $dmf_stamp ) ); ?>">
					<b><?php echo esc_html( date_i18n( 'j', $dmf_stamp ) ); ?></b>
					<span><?php echo esc_html( date_i18n( 'M', $dmf_stamp ) ); ?></span>
				</time>
			<?php endif; ?>
			<div class="dmf-events__body">
				<h3 class="dmf-events__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<?php $dmf_venue = dmf_get_field( 'event_venue' ); ?>
				<?php if ( $dmf_venue ) : ?>
					<p class="dmf-events__venue"><?php echo esc_html( $dmf_venue ); ?></p>
				<?php endif; ?>
			</div>
		</li>
	<?php endwhile; ?>
	<?php wp_reset_postdata(); ?>
</ul>
