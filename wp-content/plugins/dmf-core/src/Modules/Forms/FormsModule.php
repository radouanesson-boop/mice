<?php
/**
 * Forms module — RFP / inquiry pipeline.
 *
 * @package DMF
 */

namespace DMF\Modules\Forms;

use DMF\Modules\AbstractModule;
use DMF\Support\Options;

defined( 'ABSPATH' ) || exit;

/**
 * The conversion heart of a Convention Bureau site: every listing and CTA
 * funnels into an inquiry. Submissions are stored as a private CPT
 * (auditable pipeline in wp-admin) and emailed to the bureau. Public REST
 * endpoint hardened with nonce + honeypot + rate limiting.
 */
final class FormsModule extends AbstractModule {

	public const POST_TYPE = 'dmf_inquiry';

	public function register(): void {
		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'rest_api_init', array( $this, 'routes' ) );
	}

	public function register_cpt(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'          => array(
					'name'          => __( 'Inquiries', 'dmf' ),
					'singular_name' => __( 'Inquiry', 'dmf' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'dmf',
				'capability_type' => 'dmf_listing',
				'map_meta_cap'    => true,
				'supports'        => array( 'title', 'editor', 'custom-fields' ),
			)
		);
	}

	public function routes(): void {
		register_rest_route(
			'dmf/v1',
			'/inquiries',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create' ),
				'permission_callback' => '__return_true', // Public form; hardened below.
				'args'                => array(
					'name'       => array( 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
					'email'      => array( 'type' => 'string', 'required' => true, 'format' => 'email' ),
					'company'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
					'event_type' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
					'delegates'  => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
					'dates'      => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
					'message'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field' ),
					'listing_id' => array( 'type' => 'integer', 'default' => 0 ),
					'website'    => array( 'type' => 'string' ), // Honeypot — must stay empty.
				),
			)
		);
	}

	public function create( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		// Honeypot: bots fill every field.
		if ( '' !== (string) $request['website'] ) {
			return rest_ensure_response( array( 'ok' => true ) ); // Silent discard.
		}

		// Nonce (sent by the theme form as X-WP-Nonce or _wpnonce).
		if ( ! wp_verify_nonce( $request->get_header( 'X-DMF-Nonce' ) ?? '', 'dmf_inquiry' ) ) {
			return new \WP_Error( 'dmf_bad_nonce', __( 'Session expired — please reload the page.', 'dmf' ), array( 'status' => 403 ) );
		}

		// Rate limit: 5 submissions / 10 min / IP.
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key = 'dmf_inq_' . md5( $ip );
		$hits = (int) get_transient( $key );

		if ( $hits >= 5 ) {
			return new \WP_Error( 'dmf_rate_limited', __( 'Too many requests — please try again later.', 'dmf' ), array( 'status' => 429 ) );
		}
		set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );

		if ( ! is_email( (string) $request['email'] ) ) {
			return new \WP_Error( 'dmf_bad_email', __( 'Please provide a valid email address.', 'dmf' ), array( 'status' => 400 ) );
		}

		$listing_id = (int) $request['listing_id'];

		$inquiry_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'private',
				'post_title'   => sprintf(
					/* translators: 1: sender name, 2: date. */
					__( 'Inquiry from %1$s — %2$s', 'dmf' ),
					(string) $request['name'],
					wp_date( get_option( 'date_format' ) )
				),
				'post_content' => (string) $request['message'],
				'meta_input'   => array(
					'_dmf_inq_name'       => (string) $request['name'],
					'_dmf_inq_email'      => sanitize_email( (string) $request['email'] ),
					'_dmf_inq_company'    => (string) $request['company'],
					'_dmf_inq_event_type' => (string) $request['event_type'],
					'_dmf_inq_delegates'  => (string) $request['delegates'],
					'_dmf_inq_dates'      => (string) $request['dates'],
					'_dmf_inq_listing'    => $listing_id,
				),
			),
			true
		);

		if ( is_wp_error( $inquiry_id ) ) {
			return new \WP_Error( 'dmf_save_failed', __( 'Could not save your inquiry — please try again.', 'dmf' ), array( 'status' => 500 ) );
		}

		$to = Options::get( 'forms.recipient', get_option( 'admin_email' ) );

		wp_mail(
			$to,
			sprintf( '[%s] %s', get_bloginfo( 'name' ), __( 'New RFP / inquiry', 'dmf' ) ),
			implode(
				"\n",
				array_filter(
					array(
						__( 'Name:', 'dmf' ) . ' ' . $request['name'],
						__( 'Email:', 'dmf' ) . ' ' . $request['email'],
						$request['company'] ? __( 'Company:', 'dmf' ) . ' ' . $request['company'] : '',
						$request['event_type'] ? __( 'Event type:', 'dmf' ) . ' ' . $request['event_type'] : '',
						$request['delegates'] ? __( 'Delegates:', 'dmf' ) . ' ' . $request['delegates'] : '',
						$request['dates'] ? __( 'Dates:', 'dmf' ) . ' ' . $request['dates'] : '',
						$listing_id ? __( 'Listing:', 'dmf' ) . ' ' . get_the_title( $listing_id ) . ' — ' . get_permalink( $listing_id ) : '',
						'',
						(string) $request['message'],
						'',
						admin_url( 'post.php?post=' . $inquiry_id . '&action=edit' ),
					)
				)
			)
		);

		/**
		 * Fires after an inquiry was stored and mailed.
		 *
		 * @param int              $inquiry_id Inquiry post ID.
		 * @param \WP_REST_Request $request    Original request.
		 */
		do_action( 'dmf/inquiry_created', $inquiry_id, $request );

		return rest_ensure_response( array( 'ok' => true, 'id' => $inquiry_id ) );
	}
}
