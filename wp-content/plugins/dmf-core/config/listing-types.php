<?php
/**
 * Default listing types — the Marrakech Convention Bureau configuration.
 *
 * This file is the reference example of DMF's config-driven listing engine.
 * A new destination (Agadir, Essaouira, Casablanca…) ships its own version
 * of this file — or creates types in Destination → Listing Types — without
 * touching framework code. Everything here is data.
 *
 * Field types: text, textarea, number, email, url, tel, select,
 * multiselect, checkbox, date, time, gallery, file, geo, repeater-lite,
 * hours, social.
 *
 * @package DMF
 */

defined( 'ABSPATH' ) || exit;

// Field groups shared by most physical places.
$dmf_location_group = array(
	'label'  => __( 'Location & contact', 'dmf' ),
	'fields' => array(
		array( 'key' => 'address', 'label' => __( 'Address', 'dmf' ), 'type' => 'text' ),
		array( 'key' => 'geo', 'label' => __( 'GPS coordinates', 'dmf' ), 'type' => 'geo' ),
		array( 'key' => 'phone', 'label' => __( 'Phone', 'dmf' ), 'type' => 'tel' ),
		array( 'key' => 'email', 'label' => __( 'Email', 'dmf' ), 'type' => 'email' ),
		array( 'key' => 'website', 'label' => __( 'Website', 'dmf' ), 'type' => 'url' ),
		array( 'key' => 'languages', 'label' => __( 'Languages spoken', 'dmf' ), 'type' => 'multiselect', 'options' => array( 'en' => 'English', 'fr' => 'Français', 'ar' => 'العربية', 'es' => 'Español', 'de' => 'Deutsch', 'it' => 'Italiano', 'pt' => 'Português' ) ),
	),
);

$dmf_media_group = array(
	'label'  => __( 'Media & downloads', 'dmf' ),
	'fields' => array(
		array( 'key' => 'gallery', 'label' => __( 'Photo gallery', 'dmf' ), 'type' => 'gallery' ),
		array( 'key' => 'video_url', 'label' => __( 'Video URL', 'dmf' ), 'type' => 'url' ),
		array( 'key' => 'tour_360', 'label' => __( '360° tour URL', 'dmf' ), 'type' => 'url' ),
		array( 'key' => 'brochure', 'label' => __( 'Brochure PDF (attachment ID)', 'dmf' ), 'type' => 'file' ),
	),
);

return array(

	// --- Venues: convention centres, palais des congrès, unique venues -------
	array(
		'slug'       => 'dmf_venue',
		'singular'   => __( 'Venue', 'dmf' ),
		'plural'     => __( 'Venues', 'dmf' ),
		'rewrite'    => 'venues',
		'icon'       => 'dashicons-building',
		'kind'       => 'venue',
		'taxonomies' => array( 'dmf_category', 'dmf_area', 'dmf_amenity', 'dmf_tag' ),
		'fields'     => array(
			'capacity' => array(
				'label'  => __( 'Capacity & spaces', 'dmf' ),
				'fields' => array(
					array( 'key' => 'capacity', 'label' => __( 'Max capacity (pax)', 'dmf' ), 'type' => 'number', 'searchable' => true ),
					array( 'key' => 'meeting_rooms', 'label' => __( 'Meeting / breakout rooms', 'dmf' ), 'type' => 'number', 'searchable' => true ),
					array( 'key' => 'surface', 'label' => __( 'Total surface (m²)', 'dmf' ), 'type' => 'number' ),
					array( 'key' => 'configurations', 'label' => __( 'Configurations', 'dmf' ), 'type' => 'multiselect', 'options' => array( 'theatre' => __( 'Theatre', 'dmf' ), 'classroom' => __( 'Classroom', 'dmf' ), 'banquet' => __( 'Banquet', 'dmf' ), 'cocktail' => __( 'Cocktail', 'dmf' ), 'boardroom' => __( 'Boardroom', 'dmf' ), 'exhibition' => __( 'Exhibition', 'dmf' ) ) ),
					array( 'key' => 'operator', 'label' => __( 'Operator / brand', 'dmf' ), 'type' => 'text' ),
					array( 'key' => 'accessibility', 'label' => __( 'Wheelchair accessible', 'dmf' ), 'type' => 'checkbox' ),
					array( 'key' => 'parking', 'label' => __( 'Parking spaces', 'dmf' ), 'type' => 'number' ),
				),
			),
			'location' => $dmf_location_group,
			'media'    => $dmf_media_group,
		),
	),

	// --- Hotels: luxury hotels, riads --------------------------------------------
	array(
		'slug'       => 'dmf_hotel',
		'singular'   => __( 'Hotel', 'dmf' ),
		'plural'     => __( 'Hotels', 'dmf' ),
		'rewrite'    => 'hotels',
		'icon'       => 'dashicons-admin-multisite',
		'kind'       => 'hotel',
		'taxonomies' => array( 'dmf_category', 'dmf_area', 'dmf_amenity', 'dmf_tag' ),
		'fields'     => array(
			'hotel'    => array(
				'label'  => __( 'Hotel details', 'dmf' ),
				'fields' => array(
					array( 'key' => 'stars', 'label' => __( 'Star rating', 'dmf' ), 'type' => 'select', 'options' => array( '3' => '3★', '4' => '4★', '5' => '5★', 'palace' => __( 'Palace', 'dmf' ) ), 'searchable' => true ),
					array( 'key' => 'bedrooms', 'label' => __( 'Bedrooms', 'dmf' ), 'type' => 'number', 'searchable' => true ),
					array( 'key' => 'meeting_rooms', 'label' => __( 'Meeting rooms', 'dmf' ), 'type' => 'number', 'searchable' => true ),
					array( 'key' => 'largest_room_capacity', 'label' => __( 'Largest meeting room (pax)', 'dmf' ), 'type' => 'number' ),
					array( 'key' => 'price_range', 'label' => __( 'Price range', 'dmf' ), 'type' => 'select', 'options' => array( '$' => '$', '$$' => '$$', '$$$' => '$$$', '$$$$' => '$$$$' ) ),
					array( 'key' => 'certification', 'label' => __( 'Certifications', 'dmf' ), 'type' => 'repeater-lite', 'description' => __( 'One per line (Green Key, ISO 20121…).', 'dmf' ) ),
				),
			),
			'location' => $dmf_location_group,
			'media'    => $dmf_media_group,
		),
	),

	// --- Restaurants & gastronomy ---------------------------------------------------
	array(
		'slug'       => 'dmf_restaurant',
		'singular'   => __( 'Restaurant', 'dmf' ),
		'plural'     => __( 'Restaurants', 'dmf' ),
		'rewrite'    => 'restaurants',
		'icon'       => 'dashicons-food',
		'kind'       => 'restaurant',
		'taxonomies' => array( 'dmf_category', 'dmf_area', 'dmf_amenity', 'dmf_tag' ),
		'fields'     => array(
			'dining'   => array(
				'label'  => __( 'Dining', 'dmf' ),
				'fields' => array(
					array( 'key' => 'cuisine', 'label' => __( 'Cuisine', 'dmf' ), 'type' => 'text', 'searchable' => true ),
					array( 'key' => 'capacity', 'label' => __( 'Seated capacity', 'dmf' ), 'type' => 'number', 'searchable' => true ),
					array( 'key' => 'privatizable', 'label' => __( 'Fully privatizable', 'dmf' ), 'type' => 'checkbox' ),
					array( 'key' => 'price_range', 'label' => __( 'Price range', 'dmf' ), 'type' => 'select', 'options' => array( '$' => '$', '$$' => '$$', '$$$' => '$$$', '$$$$' => '$$$$' ) ),
					array( 'key' => 'opening_hours', 'label' => __( 'Opening hours', 'dmf' ), 'type' => 'repeater-lite' ),
				),
			),
			'location' => $dmf_location_group,
			'media'    => $dmf_media_group,
		),
	),

	// --- Experiences: incentives, team building, golf, spa, culture ------------------
	array(
		'slug'       => 'dmf_experience',
		'singular'   => __( 'Experience', 'dmf' ),
		'plural'     => __( 'Experiences', 'dmf' ),
		'rewrite'    => 'experiences',
		'icon'       => 'dashicons-palmtree',
		'kind'       => 'experience',
		'taxonomies' => array( 'dmf_category', 'dmf_area', 'dmf_tag' ),
		'fields'     => array(
			'experience' => array(
				'label'  => __( 'Experience details', 'dmf' ),
				'fields' => array(
					array( 'key' => 'duration', 'label' => __( 'Duration', 'dmf' ), 'type' => 'text' ),
					array( 'key' => 'group_size', 'label' => __( 'Max group size', 'dmf' ), 'type' => 'number', 'searchable' => true ),
					array( 'key' => 'season', 'label' => __( 'Best season', 'dmf' ), 'type' => 'multiselect', 'options' => array( 'spring' => __( 'Spring', 'dmf' ), 'summer' => __( 'Summer', 'dmf' ), 'autumn' => __( 'Autumn', 'dmf' ), 'winter' => __( 'Winter', 'dmf' ) ) ),
					array( 'key' => 'includes', 'label' => __( 'Includes', 'dmf' ), 'type' => 'repeater-lite' ),
				),
			),
			'location'   => $dmf_location_group,
			'media'      => $dmf_media_group,
		),
	),

	// --- Events: congresses, trade shows, festivals -------------------------------------
	array(
		'slug'       => 'dmf_event',
		'singular'   => __( 'Event', 'dmf' ),
		'plural'     => __( 'Events', 'dmf' ),
		'rewrite'    => 'events',
		'icon'       => 'dashicons-calendar-alt',
		'kind'       => 'event',
		'taxonomies' => array( 'dmf_category', 'dmf_tag' ),
		'fields'     => array(
			'event'    => array(
				'label'  => __( 'Event details', 'dmf' ),
				'fields' => array(
					array( 'key' => 'start_date', 'label' => __( 'Start date', 'dmf' ), 'type' => 'date', 'searchable' => true ),
					array( 'key' => 'end_date', 'label' => __( 'End date', 'dmf' ), 'type' => 'date' ),
					array( 'key' => 'event_venue', 'label' => __( 'Venue name', 'dmf' ), 'type' => 'text' ),
					array( 'key' => 'attendance', 'label' => __( 'Expected attendance', 'dmf' ), 'type' => 'number' ),
					array( 'key' => 'registration_url', 'label' => __( 'Registration URL', 'dmf' ), 'type' => 'url' ),
					array( 'key' => 'speakers', 'label' => __( 'Speakers', 'dmf' ), 'type' => 'repeater-lite', 'description' => __( 'One per line: Name — Role.', 'dmf' ) ),
				),
			),
			'location' => $dmf_location_group,
			'media'    => $dmf_media_group,
		),
	),

	// --- Suppliers: DMC, PCO, AV, transport, production, photography… ---------------------
	array(
		'slug'       => 'dmf_supplier',
		'singular'   => __( 'Supplier', 'dmf' ),
		'plural'     => __( 'Suppliers', 'dmf' ),
		'rewrite'    => 'suppliers',
		'icon'       => 'dashicons-groups',
		'kind'       => 'supplier',
		'taxonomies' => array( 'dmf_category', 'dmf_area', 'dmf_tag' ),
		'fields'     => array(
			'company'  => array(
				'label'  => __( 'Company profile', 'dmf' ),
				'fields' => array(
					array( 'key' => 'services', 'label' => __( 'Services', 'dmf' ), 'type' => 'repeater-lite' ),
					array( 'key' => 'founded', 'label' => __( 'Founded (year)', 'dmf' ), 'type' => 'number' ),
					array( 'key' => 'team_size', 'label' => __( 'Team size', 'dmf' ), 'type' => 'number' ),
					array( 'key' => 'certification', 'label' => __( 'Certifications / memberships', 'dmf' ), 'type' => 'repeater-lite' ),
					array( 'key' => 'references', 'label' => __( 'Key references', 'dmf' ), 'type' => 'repeater-lite' ),
				),
			),
			'location' => $dmf_location_group,
			'media'    => $dmf_media_group,
		),
	),
);
