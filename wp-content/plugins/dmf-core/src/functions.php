<?php
/**
 * DMF procedural developer API.
 *
 * Theme and add-on developers use these functions; everything else in the
 * framework is an implementation detail that may change between minors.
 *
 * @package DMF
 */

use DMF\Plugin;
use DMF\Support\Template;

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'dmf' ) ) {
	/**
	 * The framework kernel.
	 */
	function dmf(): Plugin {
		return Plugin::instance();
	}
}

if ( ! function_exists( 'dmf_template' ) ) {
	/**
	 * Render a theme-overridable framework template.
	 *
	 *     dmf_template( 'cards/listing', [ 'post_id' => 42 ] );
	 *
	 * Override chain: {theme}/dmf/{name}.php → {plugin}/templates/{name}.php.
	 *
	 * @param string               $name Template name.
	 * @param array<string, mixed> $args Template data ($args in the file).
	 */
	function dmf_template( string $name, array $args = array() ): void {
		Template::render( $name, $args );
	}
}

if ( ! function_exists( 'dmf_get_field' ) ) {
	/**
	 * Read a listing field (as defined in the listing type's field groups).
	 *
	 *     $capacity = dmf_get_field( 'capacity' );
	 *     $geo      = dmf_get_field( 'geo', $post_id );
	 *
	 * @param string   $key     Field key without the _dmf_ prefix.
	 * @param int|null $post_id Listing ID (defaults to current post).
	 * @return mixed
	 */
	function dmf_get_field( string $key, ?int $post_id = null ) {
		$post_id = $post_id ?: get_the_ID();
		$value   = get_post_meta( $post_id, '_dmf_' . sanitize_key( $key ), true );

		/**
		 * Filter a field read.
		 *
		 * @param mixed  $value   Stored value.
		 * @param string $key     Field key.
		 * @param int    $post_id Listing.
		 */
		return apply_filters( 'dmf/get_field', $value, $key, (int) $post_id );
	}
}

if ( ! function_exists( 'dmf_listing_types' ) ) {
	/**
	 * Active listing type post type slugs.
	 *
	 * @return string[]
	 */
	function dmf_listing_types(): array {
		return dmf()->module( 'listings' )->registry()->post_types();
	}
}

if ( ! function_exists( 'dmf_listing_kind' ) ) {
	/**
	 * Card/schema kind of a listing (venue|hotel|restaurant|experience|
	 * event|supplier|generic).
	 *
	 * @param int|null $post_id Listing ID.
	 */
	function dmf_listing_kind( ?int $post_id = null ): string {
		$post_type = get_post_type( $post_id ?: get_the_ID() );

		return dmf()->module( 'listings' )->registry()->get( (string) $post_type )?->kind ?? 'generic';
	}
}

if ( ! function_exists( 'dmf_get_card_fields' ) ) {
	/**
	 * The two or three "card meta" values for a listing, based on its kind
	 * (capacity/rooms for venues, stars/rooms for hotels, dates for events…).
	 * Themes render these as the hairline meta row.
	 *
	 * @param int $post_id Listing ID.
	 * @return array<int, array{key: string, label: string, value: string}>
	 */
	function dmf_get_card_fields( int $post_id ): array {
		$map = apply_filters(
			'dmf/card_fields_map',
			array(
				'venue'      => array( 'capacity', 'meeting_rooms' ),
				'hotel'      => array( 'stars', 'bedrooms' ),
				'restaurant' => array( 'cuisine', 'capacity' ),
				'experience' => array( 'duration', 'group_size' ),
				'event'      => array( 'start_date', 'event_venue' ),
				'supplier'   => array( 'services', 'languages' ),
				'generic'    => array( 'capacity' ),
			)
		);

		$kind   = dmf_listing_kind( $post_id );
		$keys   = $map[ $kind ] ?? $map['generic'];
		$fields = array();

		foreach ( $keys as $key ) {
			$value = dmf_get_field( $key, $post_id );

			if ( '' === $value || array() === $value || null === $value ) {
				continue;
			}

			$fields[] = array(
				'key'   => $key,
				'label' => ucfirst( str_replace( '_', ' ', $key ) ),
				'value' => is_array( $value ) ? implode( ', ', array_map( 'strval', $value ) ) : (string) $value,
			);
		}

		return $fields;
	}
}

if ( ! function_exists( 'dmf_listing_query' ) ) {
	/**
	 * Query listings for grids and sections.
	 *
	 * @param array $args {
	 *     @type string|string[] $type     Listing post type(s). Default all.
	 *     @type int             $count    Number of posts. Default 6.
	 *     @type string          $taxonomy Optional taxonomy filter.
	 *     @type string[]        $terms    Term slugs.
	 *     @type string          $orderby  date|title|rand|menu_order.
	 * }
	 */
	function dmf_listing_query( array $args = array() ): WP_Query {
		$args = wp_parse_args(
			$args,
			array(
				'type'     => dmf_listing_types(),
				'count'    => 6,
				'taxonomy' => '',
				'terms'    => array(),
				'orderby'  => 'date',
			)
		);

		$query_args = array(
			'post_type'           => $args['type'] ?: dmf_listing_types(),
			'post_status'         => 'publish',
			'posts_per_page'      => (int) $args['count'],
			'orderby'             => $args['orderby'],
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);

		if ( $args['taxonomy'] && ! empty( $args['terms'] ) ) {
			$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy' => $args['taxonomy'],
					'field'    => 'slug',
					'terms'    => (array) $args['terms'],
				),
			);
		}

		return new WP_Query( apply_filters( 'dmf/listing_query_args', $query_args, $args ) );
	}
}

if ( ! function_exists( 'dmf_breadcrumbs' ) ) {
	/**
	 * Schema-ready breadcrumb trail.
	 *
	 * @return array<int, array{name: string, url: string}>
	 */
	function dmf_breadcrumbs(): array {
		return \DMF\Modules\Seo\SeoModule::breadcrumbs();
	}
}
