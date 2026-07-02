<?php
/**
 * Block: dmf/search-bar — live search input backed by /dmf/v1/search.
 *
 * The vanilla JS behaviour (debounced fetch, results dropdown) ships with
 * the theme; this template provides accessible, functional markup that
 * also works without JS (falls back to native search).
 *
 * @var array $args { placeholder, types }
 * @package DMF
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? array(), array( 'placeholder' => '', 'types' => '' ) );

$dmf_placeholder = $args['placeholder'] ?: __( 'Search venues, hotels, experiences…', 'dmf' );
$dmf_types       = implode( ',', array_map( 'sanitize_key', array_filter( explode( ',', (string) $args['types'] ) ) ) );
?>
<form class="dmf-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"
	data-dmf-search data-endpoint="<?php echo esc_url( rest_url( 'dmf/v1/search' ) ); ?>"
	<?php echo $dmf_types ? 'data-types="' . esc_attr( $dmf_types ) . '"' : ''; ?>>

	<label class="screen-reader-text" for="dmf-search-q"><?php echo esc_html( $dmf_placeholder ); ?></label>
	<input class="dmf-search__input" id="dmf-search-q" type="search" name="s" autocomplete="off"
		placeholder="<?php echo esc_attr( $dmf_placeholder ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">

	<button class="dmf-search__submit" type="submit"><?php esc_html_e( 'Search', 'dmf' ); ?></button>

	<div class="dmf-search__results" data-dmf-search-results hidden
		role="listbox" aria-label="<?php esc_attr_e( 'Search results', 'dmf' ); ?>"></div>
</form>
