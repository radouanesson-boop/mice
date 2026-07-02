<?php
/**
 * Star rating display (visual stars + accessible text).
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? array(), array( 'rating' => 0 ) );

$dmf_rating = (float) $args['rating'];

if ( $dmf_rating <= 0 ) {
	return;
}
?>
<span class="dmf-rating" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: rating value. */ __( 'Rated %s out of 5', 'dmf' ), $dmf_rating ) ); ?>">
	<span class="dmf-rating__stars" style="--dmf-rating: <?php echo esc_attr( $dmf_rating / 5 * 100 ); ?>%;" aria-hidden="true">
		<?php
		for ( $dmf_i = 0; $dmf_i < 5; $dmf_i++ ) {
			dmf_icon( 'star' );
		}
		?>
	</span>
	<span class="dmf-rating__value" aria-hidden="true"><?php echo esc_html( number_format_i18n( $dmf_rating, 1 ) ); ?></span>
</span>
