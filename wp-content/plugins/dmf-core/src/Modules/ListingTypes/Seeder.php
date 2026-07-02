<?php
/**
 * Default destination content model.
 *
 * @package DMF
 */

namespace DMF\Modules\ListingTypes;

use DMF\Modules\Fields\FieldRegistry;

defined( 'ABSPATH' ) || exit;

/**
 * Seeds the starter content model for a MICE destination on first
 * activation: listing types + reusable field groups. Everything seeded here
 * is plain configuration — bureaus rename, extend or delete it in the admin.
 */
class Seeder {

	/**
	 * Seed types and field groups if none exist yet.
	 */
	public static function seed(): void {
		if ( ! get_option( FieldRegistry::OPTION ) ) {
			update_option( FieldRegistry::OPTION, self::field_groups() );
		}

		if ( ! get_option( TypeRegistry::OPTION ) ) {
			update_option( TypeRegistry::OPTION, self::types() );
		}
	}

	/**
	 * Starter listing types for a convention bureau.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function types(): array {
		$make = static fn( string $slug, string $name, string $singular, string $icon, array $groups, array $facets, string $card = 'listing' ): array => array(
			'slug'         => $slug,
			'name'         => $name,
			'singular'     => $singular,
			'icon'         => $icon,
			'field_groups' => $groups,
			'facets'       => $facets,
			'card'         => $card,
			'builtin'      => true,
		);

		$base = array( 'location', 'contact', 'business', 'media_plus' );

		return array(
			$make( 'convention-centers', __( 'Convention Centers', 'dmf' ), __( 'Convention Center', 'dmf' ), 'building', array_merge( $base, array( 'capacity', 'amenities' ) ), array( 'capacity', 'district', 'amenities' ), 'venue' ),
			$make( 'hotels', __( 'Hotels', 'dmf' ), __( 'Hotel', 'dmf' ), 'bed', array_merge( $base, array( 'capacity', 'hotel', 'amenities' ) ), array( 'stars', 'capacity', 'district', 'amenities' ), 'hotel' ),
			$make( 'riads', __( 'Riads', 'dmf' ), __( 'Riad', 'dmf' ), 'home', array_merge( $base, array( 'hotel', 'amenities' ) ), array( 'district', 'amenities' ), 'hotel' ),
			$make( 'venues', __( 'Unique Venues', 'dmf' ), __( 'Unique Venue', 'dmf' ), 'sparkles', array_merge( $base, array( 'capacity', 'amenities' ) ), array( 'capacity', 'district' ), 'venue' ),
			$make( 'restaurants', __( 'Restaurants & Catering', 'dmf' ), __( 'Restaurant', 'dmf' ), 'utensils', array_merge( $base, array( 'capacity', 'amenities' ) ), array( 'capacity', 'district', 'price_level' ) ),
			$make( 'dmc', __( 'DMCs & PCOs', 'dmf' ), __( 'DMC / PCO', 'dmf' ), 'briefcase', $base, array( 'languages', 'certifications' ) ),
			$make( 'event-agencies', __( 'Event Agencies', 'dmf' ), __( 'Event Agency', 'dmf' ), 'megaphone', $base, array( 'languages' ) ),
			$make( 'av-production', __( 'AV & Production', 'dmf' ), __( 'AV & Production Company', 'dmf' ), 'video', $base, array() ),
			$make( 'transport', __( 'Transport', 'dmf' ), __( 'Transport Company', 'dmf' ), 'car', $base, array() ),
			$make( 'activities', __( 'Activities & Team Building', 'dmf' ), __( 'Activity', 'dmf' ), 'compass', array_merge( $base, array( 'capacity' ) ), array( 'capacity', 'district' ), 'experience' ),
			$make( 'wellness', __( 'Golf, Spa & Wellness', 'dmf' ), __( 'Wellness Venue', 'dmf' ), 'leaf', $base, array( 'district' ), 'experience' ),
			$make( 'guides', __( 'Guides & Speakers', 'dmf' ), __( 'Guide', 'dmf' ), 'users', array( 'contact', 'business' ), array( 'languages' ) ),
		);
	}

	/**
	 * Reusable field groups shared across listing types.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function field_groups(): array {
		return array(
			'location'   => array(
				'key'    => 'location',
				'title'  => __( 'Location', 'dmf' ),
				'fields' => array(
					array( 'key' => 'address', 'label' => __( 'Address', 'dmf' ), 'type' => 'text' ),
					array( 'key' => 'city', 'label' => __( 'City', 'dmf' ), 'type' => 'text' ),
					array( 'key' => 'district', 'label' => __( 'District', 'dmf' ), 'type' => 'select', 'options' => array( 'medina' => __( 'Medina', 'dmf' ), 'gueliz' => __( 'Guéliz', 'dmf' ), 'hivernage' => __( 'Hivernage', 'dmf' ), 'palmeraie' => __( 'Palmeraie', 'dmf' ), 'agdal' => __( 'Agdal', 'dmf' ), 'outskirts' => __( 'Outskirts', 'dmf' ) ) ),
					array( 'key' => 'geo', 'label' => __( 'GPS Coordinates', 'dmf' ), 'type' => 'geo' ),
				),
			),
			'contact'    => array(
				'key'    => 'contact',
				'title'  => __( 'Contact', 'dmf' ),
				'fields' => array(
					array( 'key' => 'phone', 'label' => __( 'Phone', 'dmf' ), 'type' => 'text' ),
					array( 'key' => 'email', 'label' => __( 'Email', 'dmf' ), 'type' => 'email' ),
					array( 'key' => 'website', 'label' => __( 'Website', 'dmf' ), 'type' => 'url' ),
					array( 'key' => 'linkedin', 'label' => __( 'LinkedIn', 'dmf' ), 'type' => 'url' ),
					array( 'key' => 'instagram', 'label' => __( 'Instagram', 'dmf' ), 'type' => 'url' ),
				),
			),
			'business'   => array(
				'key'    => 'business',
				'title'  => __( 'Business Details', 'dmf' ),
				'fields' => array(
					array( 'key' => 'price_level', 'label' => __( 'Price Range', 'dmf' ), 'type' => 'select', 'options' => array( '1' => '€', '2' => '€€', '3' => '€€€', '4' => '€€€€' ) ),
					array( 'key' => 'languages', 'label' => __( 'Languages Spoken', 'dmf' ), 'type' => 'multicheck', 'options' => array( 'fr' => __( 'French', 'dmf' ), 'en' => __( 'English', 'dmf' ), 'ar' => __( 'Arabic', 'dmf' ), 'es' => __( 'Spanish', 'dmf' ), 'de' => __( 'German', 'dmf' ), 'it' => __( 'Italian', 'dmf' ), 'pt' => __( 'Portuguese', 'dmf' ) ) ),
					array( 'key' => 'certifications', 'label' => __( 'Certifications', 'dmf' ), 'type' => 'text', 'placeholder' => __( 'ISO 20121, Clef Verte…', 'dmf' ) ),
					array( 'key' => 'opening_hours', 'label' => __( 'Opening Hours', 'dmf' ), 'type' => 'hours' ),
				),
			),
			'capacity'   => array(
				'key'    => 'capacity',
				'title'  => __( 'Capacity', 'dmf' ),
				'fields' => array(
					array( 'key' => 'capacity', 'label' => __( 'Maximum Capacity', 'dmf' ), 'type' => 'number' ),
					array( 'key' => 'meeting_rooms', 'label' => __( 'Meeting Rooms', 'dmf' ), 'type' => 'number' ),
					array( 'key' => 'theater_capacity', 'label' => __( 'Theater Capacity', 'dmf' ), 'type' => 'number' ),
					array( 'key' => 'banquet_capacity', 'label' => __( 'Banquet Capacity', 'dmf' ), 'type' => 'number' ),
					array( 'key' => 'surface', 'label' => __( 'Total Surface (m²)', 'dmf' ), 'type' => 'number' ),
				),
			),
			'hotel'      => array(
				'key'    => 'hotel',
				'title'  => __( 'Accommodation', 'dmf' ),
				'fields' => array(
					array( 'key' => 'stars', 'label' => __( 'Stars', 'dmf' ), 'type' => 'select', 'options' => array( '3' => '3★', '4' => '4★', '5' => '5★', 'palace' => __( 'Palace', 'dmf' ) ) ),
					array( 'key' => 'bedrooms', 'label' => __( 'Bedrooms', 'dmf' ), 'type' => 'number' ),
				),
			),
			'amenities'  => array(
				'key'    => 'amenities',
				'title'  => __( 'Amenities', 'dmf' ),
				'fields' => array(
					array( 'key' => 'amenities', 'label' => __( 'Amenities', 'dmf' ), 'type' => 'multicheck', 'options' => array( 'wifi' => __( 'Wi-Fi', 'dmf' ), 'parking' => __( 'Parking', 'dmf' ), 'accessibility' => __( 'Wheelchair Accessible', 'dmf' ), 'av_equipment' => __( 'AV Equipment', 'dmf' ), 'outdoor' => __( 'Outdoor Space', 'dmf' ), 'pool' => __( 'Pool', 'dmf' ), 'spa' => __( 'Spa', 'dmf' ), 'restaurant' => __( 'Restaurant', 'dmf' ), 'shuttle' => __( 'Airport Shuttle', 'dmf' ) ) ),
				),
			),
			'media_plus' => array(
				'key'    => 'media_plus',
				'title'  => __( 'Rich Media', 'dmf' ),
				'fields' => array(
					array( 'key' => 'gallery', 'label' => __( 'Photo Gallery', 'dmf' ), 'type' => 'gallery' ),
					array( 'key' => 'video_url', 'label' => __( 'Video URL', 'dmf' ), 'type' => 'url' ),
					array( 'key' => 'tour_url', 'label' => __( '360° Tour URL', 'dmf' ), 'type' => 'url' ),
					array( 'key' => 'brochure', 'label' => __( 'Brochure (PDF)', 'dmf' ), 'type' => 'file' ),
				),
			),
		);
	}
}
