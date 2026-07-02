<?php
/**
 * Shortcodes module — classic-editor parity with the blocks.
 *
 * @package DMF
 */

namespace DMF\Modules\Shortcodes;

use DMF\Modules\AbstractModule;
use DMF\Support\Template;

defined( 'ABSPATH' ) || exit;

/**
 * [dmf_listings type="dmf_venue" count="6" columns="3"]
 * [dmf_search types="dmf_venue,dmf_hotel"]
 * [dmf_inquiry listing_id="123"]
 * [dmf_events count="3"]
 */
final class ShortcodesModule extends AbstractModule {

	public function register(): void {
		add_shortcode( 'dmf_listings', static fn( $atts ) => Template::render( 'blocks/listings-grid', shortcode_atts(
			array( 'type' => '', 'count' => 6, 'columns' => 3, 'taxonomy' => '', 'terms' => '' ),
			(array) $atts
		), false ) );

		add_shortcode( 'dmf_search', static fn( $atts ) => Template::render( 'blocks/search-bar', shortcode_atts(
			array( 'placeholder' => '', 'types' => '' ),
			(array) $atts
		), false ) );

		add_shortcode( 'dmf_inquiry', static fn( $atts ) => Template::render( 'blocks/inquiry-form', array(
			'listingId' => absint( shortcode_atts( array( 'listing_id' => 0 ), (array) $atts )['listing_id'] ),
		), false ) );

		add_shortcode( 'dmf_events', static fn( $atts ) => Template::render( 'blocks/events-list', shortcode_atts(
			array( 'count' => 3 ),
			(array) $atts
		), false ) );
	}
}
