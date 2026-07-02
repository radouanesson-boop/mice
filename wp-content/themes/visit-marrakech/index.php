<?php
/**
 * Generic fallback loop.
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="mcb-page-hero">
	<div class="mcb-container">
		<?php mcb_breadcrumbs(); ?>
		<h1 class="mcb-page-hero__title">
			<?php is_home() ? esc_html_e( 'Insights', 'vm' ) : the_archive_title(); ?>
		</h1>
	</div>
</div>

<div class="mcb-section">
	<div class="mcb-container">
		<?php if ( have_posts() ) : ?>
			<div class="mcb-grid mcb-grid--cols-3">
				<?php
				while ( have_posts() ) {
					the_post();
					mcb_part( mcb_is_listing() ? 'cards/card-listing' : 'cards/card-news', array( 'post_id' => get_the_ID() ) );
				}
				?>
			</div>
			<nav class="mcb-pagination" aria-label="<?php esc_attr_e( 'Pagination', 'vm' ); ?>">
				<?php the_posts_pagination(); ?>
			</nav>
		<?php else : ?>
			<p class="mcb-notice"><?php esc_html_e( 'Nothing found.', 'vm' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
