<?php
/**
 * Search results — mixed listings + content, card grid.
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
			<?php
			/* translators: %s: search query. */
			printf( esc_html__( 'Search results for “%s”', 'vm' ), esc_html( get_search_query() ) );
			?>
		</h1>
		<?php mcb_part( 'components/search-bar' ); ?>
	</div>
</div>

<div class="mcb-section">
	<div class="mcb-container">
		<?php if ( have_posts() ) : ?>
			<div class="mcb-grid mcb-grid--cols-3">
				<?php
				while ( have_posts() ) {
					the_post();

					if ( mcb_is_listing() ) {
						mcb_part( 'cards/card-' . sanitize_file_name( mcb_listing_kind() ), array( 'post_id' => get_the_ID() ) );
					} else {
						mcb_part( 'cards/card-news', array( 'post_id' => get_the_ID() ) );
					}
				}
				?>
			</div>
			<nav class="mcb-pagination" aria-label="<?php esc_attr_e( 'Pagination', 'vm' ); ?>">
				<?php the_posts_pagination(); ?>
			</nav>
		<?php else : ?>
			<p class="mcb-notice"><?php esc_html_e( 'No results — try a different keyword or browse the venues.', 'vm' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
