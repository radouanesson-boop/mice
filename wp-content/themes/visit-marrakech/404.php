<?php
/**
 * 404.
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="mcb-section" style="text-align:center">
	<div class="mcb-container mcb-container--narrow">
		<p class="mcb-eyebrow mcb-eyebrow--center">404</p>
		<h1><?php esc_html_e( 'This page has wandered into the medina', 'vm' ); ?></h1>
		<p><?php esc_html_e( 'The page you are looking for doesn’t exist — but the destination does.', 'vm' ); ?></p>
		<p><a class="mcb-btn mcb-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the homepage', 'vm' ); ?></a></p>
	</div>
</div>
<?php
get_footer();
