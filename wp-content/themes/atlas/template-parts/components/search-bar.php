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
 * Set the shortcode once via the `dmf/search_shortcode` filter, e.g.:
 *
 *     add_filter( 'dmf/search_shortcode', fn() => '[routiz_search]' );
 *
 * @package Atlas
 */

defined( 'ABSPATH' ) || exit;

$dmf_shortcode = apply_filters( 'dmf/search_shortcode', '' );
?>
<div class="dmf-search-bar">
	<?php
	if ( $dmf_shortcode && shortcode_exists( trim( $dmf_shortcode, '[]' ) ) ) {
		echo do_shortcode( $dmf_shortcode );
	} else {
		?>
		<form class="dmf-search-bar__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="dmf-search-input"><?php esc_html_e( 'Search venues, hotels and experiences', 'dmf' ); ?></label>

			<div class="dmf-search-bar__field">
				<?php dmf_icon( 'search' ); ?>
				<input id="dmf-search-input" class="dmf-search-bar__input" type="search" name="s"
					placeholder="<?php esc_attr_e( 'Search venues, hotels, experiences…', 'dmf' ); ?>"
					value="<?php echo esc_attr( get_search_query() ); ?>">
			</div>

			<?php foreach ( dmf_listing_post_types() as $dmf_pt ) : ?>
				<input type="hidden" name="post_type[]" value="<?php echo esc_attr( $dmf_pt ); ?>">
			<?php endforeach; ?>

			<button class="dmf-btn dmf-btn--primary dmf-search-bar__submit" type="submit">
				<?php esc_html_e( 'Search', 'dmf' ); ?>
			</button>
		</form>
		<?php
	}
	?>
</div>
