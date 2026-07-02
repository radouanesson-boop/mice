<?php
/**
 * Events module.
 *
 * @package DMF
 */

namespace DMF\Modules\Events;

use DMF\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Events are a listing type (dmf_event, defined in config/listing-types.php)
 * enriched with date-aware behaviour: chronological archives, upcoming
 * queries, and an iCal-friendly REST feed. Registrations/tickets are left
 * to a dedicated add-on module in the roadmap — the data model (dates,
 * venue relation, speakers, downloads) lives here.
 */
final class EventsModule extends AbstractModule {

	public const POST_TYPE = 'dmf_event';

	public function register(): void {
		add_action( 'pre_get_posts', array( $this, 'chronological_archive' ) );
		add_action( 'rest_api_init', array( $this, 'routes' ) );
	}

	/**
	 * Event archives: upcoming first, past hidden by default (filterable).
	 */
	public function chronological_archive( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( self::POST_TYPE ) ) {
			return;
		}

		$query->set( 'meta_key', '_dmf_start_date' );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'order', 'ASC' );

		if ( apply_filters( 'dmf/events_hide_past', true ) ) {
			$query->set(
				'meta_query',
				array(
					array(
						'key'     => '_dmf_start_date',
						'value'   => gmdate( 'Y-m-d' ),
						'compare' => '>=',
						'type'    => 'DATE',
					),
				)
			);
		}
	}

	public function routes(): void {
		register_rest_route(
			'dmf/v1',
			'/events/upcoming',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'upcoming' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'limit' => array( 'type' => 'integer', 'default' => 10, 'minimum' => 1, 'maximum' => 50 ),
				),
			)
		);
	}

	public function upcoming( \WP_REST_Request $request ): \WP_REST_Response {
		$query = new \WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => (int) $request['limit'],
				'no_found_rows'  => true,
				'meta_key'       => '_dmf_start_date', // phpcs:ignore WordPress.DB.SlowDBQuery
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'key'     => '_dmf_start_date',
						'value'   => gmdate( 'Y-m-d' ),
						'compare' => '>=',
						'type'    => 'DATE',
					),
				),
			)
		);

		$items = array_map(
			static fn( \WP_Post $post ) => array(
				'id'    => $post->ID,
				'title' => get_the_title( $post ),
				'url'   => get_permalink( $post ),
				'start' => get_post_meta( $post->ID, '_dmf_start_date', true ),
				'end'   => get_post_meta( $post->ID, '_dmf_end_date', true ),
				'venue' => get_post_meta( $post->ID, '_dmf_event_venue', true ),
				'image' => get_the_post_thumbnail_url( $post, 'medium_large' ) ?: null,
			),
			$query->posts
		);

		return rest_ensure_response( array( 'items' => $items ) );
	}
}
