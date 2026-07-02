<?php
/**
 * Inner-page hero: compact banner with title + breadcrumbs.
 * Rendered by inc/hooks/layout.php on non-Elementor pages.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

if ( is_search() ) {
	/* translators: %s: search query. */
	$mcb_title = sprintf( __( 'Search results for “%s”', 'mcb' ), get_search_query() );
} elseif ( is_archive() ) {
	$mcb_title = get_the_archive_title();
} elseif ( is_404() ) {
	$mcb_title = __( 'Page not found', 'mcb' );
} else {
	$mcb_title = get_the_title();
}
?>
<section class="mcb-page-hero">
	<div class="mcb-container">
		<?php mcb_breadcrumbs(); ?>
		<h1 class="mcb-page-hero__title"><?php echo wp_kses_post( $mcb_title ); ?></h1>
		<?php if ( is_archive() && get_the_archive_description() ) : ?>
			<div class="mcb-page-hero__desc"><?php the_archive_description(); ?></div>
		<?php endif; ?>
	</div>
</section>
