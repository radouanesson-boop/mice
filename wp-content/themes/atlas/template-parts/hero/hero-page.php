<?php
/**
 * Inner-page hero: compact banner with title + breadcrumbs.
 * Rendered by inc/hooks/layout.php on non-Elementor pages.
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

if ( is_search() ) {
	/* translators: %s: search query. */
	$dmf_title = sprintf( __( 'Search results for “%s”', 'dmf' ), get_search_query() );
} elseif ( is_archive() ) {
	$dmf_title = get_the_archive_title();
} elseif ( is_404() ) {
	$dmf_title = __( 'Page not found', 'dmf' );
} else {
	$dmf_title = get_the_title();
}
?>
<section class="dmf-page-hero">
	<div class="dmf-container">
		<?php dmf_breadcrumbs(); ?>
		<h1 class="dmf-page-hero__title"><?php echo wp_kses_post( $dmf_title ); ?></h1>
		<?php if ( is_archive() && get_the_archive_description() ) : ?>
			<div class="dmf-page-hero__desc"><?php the_archive_description(); ?></div>
		<?php endif; ?>
	</div>
</section>
