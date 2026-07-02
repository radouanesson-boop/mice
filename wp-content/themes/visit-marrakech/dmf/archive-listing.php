<?php
/**
 * DMF override — listing archives in the Visit Marrakech design:
 * paper page hero + live search + card grid.
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="mcb-page-hero">
	<div class="mcb-container">
		<?php mcb_breadcrumbs(); ?>
		<h1 class="mcb-page-hero__title"><?php post_type_archive_title(); ?></h1>
		<?php the_archive_description( '<div class="mcb-page-hero__desc">', '</div>' ); ?>
		<?php mcb_part( 'components/search-bar', array( 'types' => (string) get_query_var( 'post_type' ) ) ); ?>
	</div>
</div>

<div class="mcb-section">
	<div class="mcb-container">
		<?php if ( have_posts() ) : ?>
			<div class="mcb-grid mcb-grid--cols-3" data-dmf-results>
				<?php
				while ( have_posts() ) {
					the_post();
					dmf_template( 'cards/listing', array( 'post_id' => get_the_ID() ) );
				}
				?>
			</div>
			<nav class="mcb-pagination" aria-label="<?php esc_attr_e( 'Listings pagination', 'vm' ); ?>">
				<?php the_posts_pagination(); ?>
			</nav>
		<?php else : ?>
			<p class="mcb-notice"><?php esc_html_e( 'No listings found.', 'vm' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
