<?php
/**
 * SEO module — structured data, OpenGraph, breadcrumbs.
 *
 * @package DMF
 */

namespace DMF\Modules\Seo;

use DMF\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Enterprise SEO baseline. Defers to Yoast/RankMath when active (no
 * duplicate tags); otherwise emits schema.org JSON-LD and OpenGraph for
 * listings and events. Schema type maps from the listing type "kind".
 */
final class SeoModule extends AbstractModule {

	public function register(): void {
		add_action( 'wp_head', array( $this, 'jsonld' ), 5 );

		if ( ! defined( 'WPSEO_VERSION' ) && ! defined( 'RANK_MATH_VERSION' ) ) {
			add_action( 'wp_head', array( $this, 'opengraph' ), 6 );
		}
	}

	/**
	 * Schema.org type per listing kind.
	 */
	private function schema_type( string $kind ): string {
		return match ( $kind ) {
			'venue'      => 'EventVenue',
			'hotel'      => 'Hotel',
			'restaurant' => 'Restaurant',
			'event'      => 'Event',
			'experience' => 'TouristAttraction',
			'supplier'   => 'LocalBusiness',
			default      => 'LocalBusiness',
		};
	}

	/**
	 * JSON-LD for single listings (LocalBusiness family / Event) and the
	 * site-wide Organization node on the front page.
	 */
	public function jsonld(): void {
		if ( is_front_page() ) {
			$this->print_jsonld(
				array(
					'@context' => 'https://schema.org',
					'@type'    => 'GovernmentOrganization',
					'name'     => get_bloginfo( 'name' ),
					'url'      => home_url( '/' ),
					'logo'     => get_site_icon_url() ?: null,
				)
			);
		}

		$registry = dmf()->module( 'listings' )->registry();

		if ( ! is_singular( $registry->post_types() ) ) {
			return;
		}

		$post = get_queried_object();
		$type = $registry->get( $post->post_type );
		$geo  = get_post_meta( $post->ID, '_dmf_geo', true );

		$node = array(
			'@context'    => 'https://schema.org',
			'@type'       => $this->schema_type( $type->kind ?? 'generic' ),
			'name'        => get_the_title( $post ),
			'url'         => get_permalink( $post ),
			'description' => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'image'       => get_the_post_thumbnail_url( $post, 'large' ) ?: null,
			'telephone'   => get_post_meta( $post->ID, '_dmf_phone', true ) ?: null,
			'address'     => get_post_meta( $post->ID, '_dmf_address', true ) ?: null,
		);

		if ( is_array( $geo ) && '' !== ( $geo['lat'] ?? '' ) ) {
			$node['geo'] = array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) $geo['lat'],
				'longitude' => (float) $geo['lng'],
			);
		}

		if ( 'event' === ( $type->kind ?? '' ) ) {
			$node['startDate'] = get_post_meta( $post->ID, '_dmf_start_date', true ) ?: null;
			$node['endDate']   = get_post_meta( $post->ID, '_dmf_end_date', true ) ?: null;
			$node['location']  = get_post_meta( $post->ID, '_dmf_event_venue', true ) ?: null;
		}

		$rating = get_post_meta( $post->ID, '_dmf_rating', true );
		if ( $rating ) {
			$node['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => (float) $rating,
				'reviewCount' => (int) get_post_meta( $post->ID, '_dmf_rating_count', true ),
			);
		}

		/**
		 * Filter a listing's JSON-LD node before output.
		 *
		 * @param array    $node Schema node.
		 * @param \WP_Post $post Listing.
		 */
		$this->print_jsonld( apply_filters( 'dmf/jsonld', $node, $post ) );
	}

	private function print_jsonld( array $node ): void {
		echo '<script type="application/ld+json">' .
			wp_json_encode( array_filter( $node, static fn( $v ) => null !== $v && '' !== $v ) ) .
			'</script>' . "\n";
	}

	/**
	 * Minimal OpenGraph/Twitter tags when no SEO plugin is active.
	 */
	public function opengraph(): void {
		$title = wp_get_document_title();
		$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
		$image = is_singular() ? get_the_post_thumbnail_url( null, 'large' ) : get_site_icon_url();

		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta property="og:type" content="%s">' . "\n", is_singular() ? 'article' : 'website' );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
		printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );

		if ( $image ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		}

		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	}

	/**
	 * Breadcrumb trail (schema-ready) for themes to render.
	 *
	 * @return array<int, array{name: string, url: string}>
	 */
	public static function breadcrumbs(): array {
		$trail = array( array( 'name' => __( 'Home', 'dmf' ), 'url' => home_url( '/' ) ) );

		if ( is_singular() ) {
			$post      = get_queried_object();
			$archive   = get_post_type_archive_link( $post->post_type );
			$type_name = get_post_type_object( $post->post_type )->labels->name ?? '';

			if ( $archive && $type_name ) {
				$trail[] = array( 'name' => $type_name, 'url' => $archive );
			}

			$trail[] = array( 'name' => get_the_title( $post ), 'url' => get_permalink( $post ) );
		} elseif ( is_archive() ) {
			$trail[] = array( 'name' => wp_strip_all_tags( get_the_archive_title() ), 'url' => '' );
		}

		return apply_filters( 'dmf/breadcrumbs', $trail );
	}
}
