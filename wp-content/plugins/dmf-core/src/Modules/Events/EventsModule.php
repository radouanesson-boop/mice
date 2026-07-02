<?php
/**
 * Events module.
 *
 * @package DMF
 */

namespace DMF\Modules\Events;

use DMF\Container;
use DMF\Support\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Business-events calendar: congresses, trade shows, fam trips, workshops.
 * Events are a dedicated CPT with schedule meta, an optional venue relation
 * to a listing, and simple recurrence (daily/weekly/monthly/yearly) that
 * rolls occurrences forward via the daily framework cron.
 */
class EventsModule extends AbstractModule {

	public const POST_TYPE = 'dmf_event';
	public const TAXONOMY  = 'dmf_event_category';

	/**
	 * Schedule meta keys => sanitizers.
	 *
	 * @var array<string, callable>
	 */
	private const META = array(
		'_dmf_event_start'      => 'sanitize_text_field', // Y-m-d H:i
		'_dmf_event_end'        => 'sanitize_text_field',
		'_dmf_event_all_day'    => 'rest_sanitize_boolean',
		'_dmf_event_recurrence' => 'sanitize_key',         // none|daily|weekly|monthly|yearly
		'_dmf_event_until'      => 'sanitize_text_field', // Y-m-d
		'_dmf_event_venue_id'   => 'absint',
		'_dmf_event_location'   => 'sanitize_text_field',
		'_dmf_event_tickets'    => 'esc_url_raw',
		'_dmf_event_register'   => 'esc_url_raw',
	);

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'events';
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		add_action( 'init', array( $this, 'register_types' ), 5 );
		add_action( 'add_meta_boxes_' . self::POST_TYPE, array( $this, 'register_metabox' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'dmf/cron/reindex', array( $this, 'roll_recurrences' ) );
	}

	/**
	 * Register the event content model.
	 */
	public function register_types(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'          => array(
					'name'          => __( 'Events', 'dmf' ),
					'singular_name' => __( 'Event', 'dmf' ),
					'add_new_item'  => __( 'Add New Event', 'dmf' ),
				),
				'public'          => true,
				'menu_icon'       => 'dashicons-calendar-alt',
				'menu_position'   => 26,
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
				'has_archive'     => 'events',
				'rewrite'         => array( 'slug' => 'event', 'with_front' => false ),
				'capability_type' => 'dmf_event',
				'map_meta_cap'    => true,
				'show_in_rest'    => true,
				'rest_base'       => 'dmf-events',
			)
		);

		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'Event Categories', 'dmf' ),
					'singular_name' => __( 'Event Category', 'dmf' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'event-category', 'with_front' => false ),
			)
		);
	}

	/**
	 * Schedule meta box.
	 */
	public function register_metabox(): void {
		add_meta_box( 'dmf-event-schedule', __( 'Event Schedule', 'dmf' ), array( $this, 'render_metabox' ), self::POST_TYPE, 'normal', 'high' );
	}

	/**
	 * Render the schedule meta box.
	 *
	 * @param \WP_Post $post Event post.
	 */
	public function render_metabox( \WP_Post $post ): void {
		wp_nonce_field( 'dmf_save_event', 'dmf_event_nonce' );

		$value = static fn( string $key ): string => (string) get_post_meta( $post->ID, $key, true );

		$recurrences = array(
			'none'    => __( 'One-off', 'dmf' ),
			'daily'   => __( 'Daily', 'dmf' ),
			'weekly'  => __( 'Weekly', 'dmf' ),
			'monthly' => __( 'Monthly', 'dmf' ),
			'yearly'  => __( 'Yearly', 'dmf' ),
		);

		echo '<table class="form-table" role="presentation">';

		printf(
			'<tr><th scope="row"><label for="dmf_event_start">%s</label></th><td><input type="datetime-local" id="dmf_event_start" name="dmf_event[_dmf_event_start]" value="%s" /></td></tr>',
			esc_html__( 'Starts', 'dmf' ),
			esc_attr( str_replace( ' ', 'T', $value( '_dmf_event_start' ) ) )
		);
		printf(
			'<tr><th scope="row"><label for="dmf_event_end">%s</label></th><td><input type="datetime-local" id="dmf_event_end" name="dmf_event[_dmf_event_end]" value="%s" /></td></tr>',
			esc_html__( 'Ends', 'dmf' ),
			esc_attr( str_replace( ' ', 'T', $value( '_dmf_event_end' ) ) )
		);
		printf(
			'<tr><th scope="row">%s</th><td><label><input type="checkbox" name="dmf_event[_dmf_event_all_day]" value="1" %s /> %s</label></td></tr>',
			esc_html__( 'All day', 'dmf' ),
			checked( (bool) $value( '_dmf_event_all_day' ), true, false ),
			esc_html__( 'This event runs all day', 'dmf' )
		);

		printf( '<tr><th scope="row"><label for="dmf_event_recurrence">%s</label></th><td><select id="dmf_event_recurrence" name="dmf_event[_dmf_event_recurrence]">', esc_html__( 'Repeats', 'dmf' ) );
		foreach ( $recurrences as $key => $label ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $value( '_dmf_event_recurrence' ), $key, false ), esc_html( $label ) );
		}
		echo '</select></td></tr>';

		printf(
			'<tr><th scope="row"><label for="dmf_event_until">%s</label></th><td><input type="date" id="dmf_event_until" name="dmf_event[_dmf_event_until]" value="%s" /></td></tr>',
			esc_html__( 'Repeats until', 'dmf' ),
			esc_attr( $value( '_dmf_event_until' ) )
		);
		printf(
			'<tr><th scope="row"><label for="dmf_event_venue_id">%s</label></th><td><input type="number" id="dmf_event_venue_id" name="dmf_event[_dmf_event_venue_id]" value="%s" /> <span class="description">%s</span></td></tr>',
			esc_html__( 'Venue listing ID', 'dmf' ),
			esc_attr( $value( '_dmf_event_venue_id' ) ),
			esc_html__( 'Link the event to a venue listing.', 'dmf' )
		);
		printf(
			'<tr><th scope="row"><label for="dmf_event_location">%s</label></th><td><input type="text" class="regular-text" id="dmf_event_location" name="dmf_event[_dmf_event_location]" value="%s" /></td></tr>',
			esc_html__( 'Location (free text)', 'dmf' ),
			esc_attr( $value( '_dmf_event_location' ) )
		);
		printf(
			'<tr><th scope="row"><label for="dmf_event_register">%s</label></th><td><input type="url" class="regular-text" id="dmf_event_register" name="dmf_event[_dmf_event_register]" value="%s" /></td></tr>',
			esc_html__( 'Registration URL', 'dmf' ),
			esc_attr( $value( '_dmf_event_register' ) )
		);
		printf(
			'<tr><th scope="row"><label for="dmf_event_tickets">%s</label></th><td><input type="url" class="regular-text" id="dmf_event_tickets" name="dmf_event[_dmf_event_tickets]" value="%s" /></td></tr>',
			esc_html__( 'Tickets URL', 'dmf' ),
			esc_attr( $value( '_dmf_event_tickets' ) )
		);

		echo '</table>';
	}

	/**
	 * Persist schedule meta.
	 *
	 * @param int      $post_id Event ID.
	 * @param \WP_Post $post    Event post.
	 */
	public function save( int $post_id, \WP_Post $post ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST['dmf_event_nonce'] ) || ! wp_verify_nonce( sanitize_key( (string) wp_unslash( $_POST['dmf_event_nonce'] ) ), 'dmf_save_event' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$submitted = isset( $_POST['dmf_event'] ) ? (array) wp_unslash( $_POST['dmf_event'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized per key below.

		foreach ( self::META as $key => $sanitize ) {
			$raw = $submitted[ $key ] ?? '';
			if ( in_array( $key, array( '_dmf_event_start', '_dmf_event_end' ), true ) ) {
				$raw = str_replace( 'T', ' ', (string) $raw );
			}
			$clean = $sanitize( $raw );
			if ( '' === $clean || 0 === $clean || false === $clean ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $clean );
			}
		}
	}

	/**
	 * Upcoming events query.
	 *
	 * @param int    $limit    Max events.
	 * @param string $category Event category slug.
	 */
	public static function upcoming( int $limit = 6, string $category = '' ): \WP_Query {
		$args = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'meta_key'       => '_dmf_event_start',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_dmf_event_start',
					'value'   => current_time( 'mysql' ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			),
		);

		if ( $category ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => self::TAXONOMY,
					'field'    => 'slug',
					'terms'    => sanitize_title( $category ),
				),
			);
		}

		return new \WP_Query( $args );
	}

	/**
	 * Roll recurring events forward: once an occurrence has passed, advance
	 * start/end by the recurrence interval until in the future or expired.
	 */
	public function roll_recurrences(): void {
		$events = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => '_dmf_event_recurrence',
						'value'   => array( 'daily', 'weekly', 'monthly', 'yearly' ),
						'compare' => 'IN',
					),
					array(
						'key'     => '_dmf_event_start',
						'value'   => current_time( 'mysql' ),
						'compare' => '<',
						'type'    => 'DATETIME',
					),
				),
			)
		);

		foreach ( $events as $event_id ) {
			$this->roll_event( (int) $event_id );
		}
	}

	/**
	 * Advance one event's occurrence.
	 *
	 * @param int $event_id Event ID.
	 */
	private function roll_event( int $event_id ): void {
		$start      = (string) get_post_meta( $event_id, '_dmf_event_start', true );
		$end        = (string) get_post_meta( $event_id, '_dmf_event_end', true );
		$recurrence = (string) get_post_meta( $event_id, '_dmf_event_recurrence', true );
		$until      = (string) get_post_meta( $event_id, '_dmf_event_until', true );

		$interval = match ( $recurrence ) {
			'daily'   => '+1 day',
			'weekly'  => '+1 week',
			'monthly' => '+1 month',
			'yearly'  => '+1 year',
			default   => '',
		};

		if ( '' === $interval || '' === $start ) {
			return;
		}

		$now       = strtotime( current_time( 'mysql' ) );
		$start_ts  = strtotime( $start );
		$end_ts    = $end ? strtotime( $end ) : false;
		$until_ts  = $until ? strtotime( $until . ' 23:59:59' ) : false;
		$guard     = 0;

		while ( false !== $start_ts && $start_ts < $now && $guard < 1000 ) {
			$start_ts = strtotime( $interval, $start_ts );
			if ( false !== $end_ts ) {
				$end_ts = strtotime( $interval, $end_ts );
			}
			++$guard;
		}

		if ( false === $start_ts || ( false !== $until_ts && $start_ts > $until_ts ) ) {
			// Series over — stop recurring so it archives naturally.
			update_post_meta( $event_id, '_dmf_event_recurrence', 'none' );
			return;
		}

		update_post_meta( $event_id, '_dmf_event_start', gmdate( 'Y-m-d H:i', $start_ts ) );
		if ( false !== $end_ts ) {
			update_post_meta( $event_id, '_dmf_event_end', gmdate( 'Y-m-d H:i', $end_ts ) );
		}
	}
}
