<?php
/**
 * Star rating display (visual stars + accessible text).
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? array(), array( 'rating' => 0 ) );

$mcb_rating = (float) $args['rating'];

if ( $mcb_rating <= 0 ) {
	return;
}
?>
<span class="mcb-rating" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: rating value. */ __( 'Rated %s out of 5', 'mcb' ), $mcb_rating ) ); ?>">
	<span class="mcb-rating__stars" style="--mcb-rating: <?php echo esc_attr( $mcb_rating / 5 * 100 ); ?>%;" aria-hidden="true">
		<?php
		for ( $mcb_i = 0; $mcb_i < 5; $mcb_i++ ) {
			mcb_icon( 'star' );
		}
		?>
	</span>
	<span class="mcb-rating__value" aria-hidden="true"><?php echo esc_html( number_format_i18n( $mcb_rating, 1 ) ); ?></span>
</span>
