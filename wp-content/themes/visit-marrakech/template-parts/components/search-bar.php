<?php
/**
 * Search bar — wraps the DMF Core live-search component (REST-backed,
 * theme-styled). Falls back to native WP search when the framework is
 * inactive.
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? array(), array( 'types' => '' ) );
?>
<div class="mcb-search-bar">
	<?php
	if ( function_exists( 'dmf_template' ) ) {
		dmf_template(
			'blocks/search-bar',
			array(
				'types'       => $args['types'],
				'placeholder' => __( 'Search venues, hotels, experiences…', 'vm' ),
			)
		);
	} else {
		?>
		<form class="dmf-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="vm-search-q"><?php esc_html_e( 'Search', 'vm' ); ?></label>
			<input class="dmf-search__input" id="vm-search-q" type="search" name="s"
				placeholder="<?php esc_attr_e( 'Search…', 'vm' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
			<button class="dmf-search__submit" type="submit"><?php esc_html_e( 'Search', 'vm' ); ?></button>
		</form>
		<?php
	}
	?>
</div>
