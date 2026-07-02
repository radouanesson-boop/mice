<?php
/**
 * Hero search bar.
 *
 * Preference order:
 *  1. Routiz/Brikk search form (keeps ajax filters, categories, geolocation
 *     working exactly as the plugin intends) — rendered via its shortcode
 *     when available, then re-skinned by CSS.
 *  2. Native WP search scoped to listing post types as a graceful fallback.
 *
 * Set the shortcode once via the `mcb/search_shortcode` filter, e.g.:
 *
 *     add_filter( 'mcb/search_shortcode', fn() => '[routiz_search]' );
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$mcb_shortcode = apply_filters( 'mcb/search_shortcode', '' );
?>
<div class="mcb-search-bar">
	<?php
	if ( $mcb_shortcode && shortcode_exists( trim( $mcb_shortcode, '[]' ) ) ) {
		echo do_shortcode( $mcb_shortcode );
	} else {
		?>
		<form class="mcb-search-bar__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="mcb-search-input"><?php esc_html_e( 'Search venues, hotels and experiences', 'mcb' ); ?></label>

			<div class="mcb-search-bar__field">
				<?php mcb_icon( 'search' ); ?>
				<input id="mcb-search-input" class="mcb-search-bar__input" type="search" name="s"
					placeholder="<?php esc_attr_e( 'Search venues, hotels, experiences…', 'mcb' ); ?>"
					value="<?php echo esc_attr( get_search_query() ); ?>">
			</div>

			<?php foreach ( mcb_listing_post_types() as $mcb_pt ) : ?>
				<input type="hidden" name="post_type[]" value="<?php echo esc_attr( $mcb_pt ); ?>">
			<?php endforeach; ?>

			<button class="mcb-btn mcb-btn--primary mcb-search-bar__submit" type="submit">
				<?php esc_html_e( 'Search', 'mcb' ); ?>
			</button>
		</form>
		<?php
	}
	?>
</div>
