<?php
/**
 * SEO module.
 *
 * @package DMF
 */

namespace DMF\Modules\Seo;

use DMF\Container;
use DMF\Modules\Events\EventsModule;
use DMF\Modules\Fields\FieldRegistry;
use DMF\Modules\Listings\PostTypes;
use DMF\Support\AbstractModule;
use DMF\Support\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Structured data and social meta for destination content. Defers politely:
 * when a dedicated SEO plugin (Yoast, RankMath, SEOPress) is active, only
 * the listing/event JSON-LD (which they can't know about) is emitted.
 */
class SeoModule extends AbstractModule {

	/**
	 * Settings accessor.
	 */
	private Options $options;

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'seo';
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		$this->options = $container->get( Options::class );

		if ( $this->options->get( 'seo.schema', true ) ) {
			add_action( 'wp_head', array( $this, 'json_ld' ), 5 );
		}

		if ( $this->options->get( 'seo.opengraph', true ) && ! $this->seo_plugin_active() ) {
			add_action( 'wp_head', array( $this, 'opengraph' ), 6 );
		}
	}

	/**
	 * Whether a dedicated SEO plugin owns the head.
	 */
	private function seo_plugin_active(): bool {
		return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'SEOPRESS_VERSION' );
	}

	/**
	 * Emit JSON-LD for listings and events.
	 */
	public function json_ld(): void {
		$schema = null;

		if ( is_singular( PostTypes::LISTING ) ) {
			$schema = $this->listing_schema( get_the_ID() );
		} elseif ( is_singular( EventsModule::POST_TYPE ) ) {
			$schema = $this->event_schema( get_the_ID() );
		}

		/**
		 * Filter the JSON-LD payload before output.
		 *
		 * @param array<string, mixed>|null $schema Schema.org graph.
		 */
		$schema = apply_filters( 'dmf/seo/schema', $schema );

		if ( $schema ) {
			printf(
				'<script type="application/ld+json">%s</script>' . "\n",
				wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) // phpcs:ignore WordPress.Security.EscapeOutput -- JSON-encoded structured data.
			);
		}
	}

	/**
	 * Schema.org node for a listing. @type is derived from the listing type.
	 *
	 * @param int $post_id Listing ID.
	 * @return array<string, mixed>
	 */
	public function listing_schema( int $post_id ): array {
		$type_slug = PostTypes::listing_type_slug( $post_id );

		$type_map = array(
			'hotels'             => 'Hotel',
			'riads'              => 'Hotel',
			'restaurants'        => 'Restaurant',
			'convention-centers' => 'EventVenue',
			'venues'             => 'EventVenue',
			'wellness'           => 'HealthAndBeautyBusiness',
			'transport'          => 'LocalBusiness',
		);

		/**
		 * Filter the listing-type → schema.org type map.
		 *
		 * @param array<string, string> $type_map slug => schema.org @type.
		 */
		$type_map = (array) apply_filters( 'dmf/seo/schema_type_map', $type_map );

		$geo = (array) FieldRegistry::value( $post_id, 'geo', array() );

		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => $type_map[ $type_slug ] ?? 'LocalBusiness',
			'@id'         => get_permalink( $post_id ) . '#listing',
			'name'        => get_the_title( $post_id ),
			'description' => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
			'url'         => get_permalink( $post_id ),
		);

		if ( has_post_thumbnail( $post_id ) ) {
			$schema['image'] = get_the_post_thumbnail_url( $post_id, 'large' );
		}

		$address = (string) FieldRegistry::value( $post_id, 'address' );
		$city    = (string) FieldRegistry::value( $post_id, 'city', (string) $this->options->get( 'general.default_city' ) );
		if ( $address || $city ) {
			$schema['address'] = array_filter(
				array(
					'@type'           => 'PostalAddress',
					'streetAddress'   => $address,
					'addressLocality' => $city,
					'addressCountry'  => (string) $this->options->get( 'general.default_country', 'MA' ),
				)
			);
		}

		if ( isset( $geo['lat'], $geo['lng'] ) ) {
			$schema['geo'] = array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) $geo['lat'],
				'longitude' => (float) $geo['lng'],
			);
		}

		$phone = (string) FieldRegistry::value( $post_id, 'phone' );
		if ( $phone ) {
			$schema['telephone'] = $phone;
		}

		$rating = (float) get_post_meta( $post_id, '_dmf_rating', true );
		$count  = (int) get_post_meta( $post_id, '_dmf_review_count', true );
		if ( $rating > 0 && $count > 0 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => $rating,
				'reviewCount' => $count,
				'bestRating'  => 5,
			);
		}

		return $schema;
	}

	/**
	 * Schema.org Event node.
	 *
	 * @param int $post_id Event ID.
	 * @return array<string, mixed>
	 */
	public function event_schema( int $post_id ): array {
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'BusinessEvent',
			'name'        => get_the_title( $post_id ),
			'description' => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
			'url'         => get_permalink( $post_id ),
		);

		$start = (string) get_post_meta( $post_id, '_dmf_event_start', true );
		$end   = (string) get_post_meta( $post_id, '_dmf_event_end', true );
		if ( $start ) {
			$schema['startDate'] = str_replace( ' ', 'T', $start );
		}
		if ( $end ) {
			$schema['endDate'] = str_replace( ' ', 'T', $end );
		}

		if ( has_post_thumbnail( $post_id ) ) {
			$schema['image'] = get_the_post_thumbnail_url( $post_id, 'large' );
		}

		$venue_id = (int) get_post_meta( $post_id, '_dmf_event_venue_id', true );
		$location = (string) get_post_meta( $post_id, '_dmf_event_location', true );

		if ( $venue_id && PostTypes::LISTING === get_post_type( $venue_id ) ) {
			$schema['location'] = array(
				'@type' => 'EventVenue',
				'name'  => get_the_title( $venue_id ),
				'url'   => get_permalink( $venue_id ),
			);
		} elseif ( $location ) {
			$schema['location'] = array(
				'@type' => 'Place',
				'name'  => $location,
			);
		}

		return $schema;
	}

	/**
	 * OpenGraph / Twitter card tags (only when no SEO plugin is present).
	 */
	public function opengraph(): void {
		if ( ! is_singular() ) {
			return;
		}

		$post_id = get_the_ID();

		$tags = array(
			'og:type'        => 'article',
			'og:site_name'   => get_bloginfo( 'name' ),
			'og:title'       => get_the_title( $post_id ),
			'og:description' => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
			'og:url'         => get_permalink( $post_id ),
			'og:locale'      => get_locale(),
		);

		if ( has_post_thumbnail( $post_id ) ) {
			$tags['og:image'] = (string) get_the_post_thumbnail_url( $post_id, 'large' );
		}

		/**
		 * Filter OpenGraph tags.
		 *
		 * @param array<string, string> $tags    property => content.
		 * @param int                   $post_id Current post.
		 */
		$tags = (array) apply_filters( 'dmf/seo/opengraph', $tags, $post_id );

		foreach ( $tags as $property => $content ) {
			if ( '' === (string) $content ) {
				continue;
			}
			printf( '<meta property="%s" content="%s" />' . "\n", esc_attr( $property ), esc_attr( (string) $content ) );
		}

		printf( '<meta name="twitter:card" content="summary_large_image" />' . "\n" );
	}
}
