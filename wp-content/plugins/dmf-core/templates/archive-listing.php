<?php
/**
 * Listing archive — framework fallback template.
 *
 * Themes override via {theme}/dmf/archive-listing.php or a native
 * archive-{post_type}.php.
 *
 * @package DMF
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="dmf-content" class="dmf-archive">
	<header class="dmf-archive__header">
		<h1 class="dmf-archive__title"><?php post_type_archive_title(); ?></h1>
		<?php the_archive_description( '<div class="dmf-archive__desc">', '</div>' ); ?>
	</header>

	<?php dmf_template( 'blocks/search-bar', array( 'types' => get_query_var( 'post_type' ) ) ); ?>

	<?php if ( have_posts() ) : ?>
		<div class="dmf-grid" data-dmf-results>
			<?php
			while ( have_posts() ) {
				the_post();
				dmf_template( 'cards/listing', array( 'post_id' => get_the_ID() ) );
			}
			?>
		</div>

		<nav class="dmf-pagination" aria-label="<?php esc_attr_e( 'Listings pagination', 'dmf' ); ?>">
			<?php the_posts_pagination(); ?>
		</nav>
	<?php else : ?>
		<p class="dmf-empty"><?php esc_html_e( 'No listings found.', 'dmf' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
