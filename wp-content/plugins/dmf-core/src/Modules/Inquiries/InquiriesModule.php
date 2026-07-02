<?php
/**
 * Inquiries module.
 *
 * @package DMF
 */

namespace DMF\Modules\Inquiries;

use DMF\Container;
use DMF\Modules\Installer\Schema;
use DMF\Support\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * The RFP / lead pipeline. Every "Request a proposal" or contact form
 * submission becomes an inquiry row with a status workflow
 * (new → in_progress → answered → closed) visible to bureau staff, and
 * optionally to the partner that owns the listing.
 */
class InquiriesModule extends AbstractModule {

	public const STATUSES = array( 'new', 'in_progress', 'answered', 'closed', 'spam' );

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'inquiries';
	}

	/**
	 * Create an inquiry.
	 *
	 * @param array<string, mixed> $data Raw form data.
	 * @return int|\WP_Error New inquiry ID.
	 */
	public function create( array $data ): int|\WP_Error {
		global $wpdb;

		$name    = sanitize_text_field( (string) ( $data['name'] ?? '' ) );
		$email   = sanitize_email( (string) ( $data['email'] ?? '' ) );
		$message = sanitize_textarea_field( (string) ( $data['message'] ?? '' ) );

		if ( '' === $name || ! is_email( $email ) || '' === $message ) {
			return new \WP_Error( 'dmf_invalid_inquiry', __( 'Name, a valid email and a message are required.', 'dmf' ), array( 'status' => 400 ) );
		}

		$event_date = sanitize_text_field( (string) ( $data['event_date'] ?? '' ) );
		if ( $event_date && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $event_date ) ) {
			$event_date = '';
		}

		$row = array(
			'listing_id' => absint( $data['listing_id'] ?? 0 ),
			'user_id'    => get_current_user_id(),
			'name'       => $name,
			'email'      => $email,
			'phone'      => sanitize_text_field( (string) ( $data['phone'] ?? '' ) ),
			'company'    => sanitize_text_field( (string) ( $data['company'] ?? '' ) ),
			'country'    => strtoupper( substr( sanitize_text_field( (string) ( $data['country'] ?? '' ) ), 0, 2 ) ),
			'event_date' => $event_date ?: null,
			'attendees'  => absint( $data['attendees'] ?? 0 ),
			'message'    => $message,
			'status'     => 'new',
			'source'     => sanitize_key( (string) ( $data['source'] ?? 'web' ) ),
			'created_at' => current_time( 'mysql', true ),
		);

		$inserted = $wpdb->insert( Schema::table( 'inquiries' ), $row ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		if ( ! $inserted ) {
			return new \WP_Error( 'dmf_inquiry_failed', __( 'Your inquiry could not be saved. Please try again.', 'dmf' ), array( 'status' => 500 ) );
		}

		$id = (int) $wpdb->insert_id;

		/**
		 * Fires when a new inquiry lands. Notifications listens here.
		 *
		 * @param int                  $id  Inquiry ID.
		 * @param array<string, mixed> $row Stored row.
		 */
		do_action( 'dmf/inquiries/created', $id, $row );

		return $id;
	}

	/**
	 * Paged inquiries for the admin pipeline.
	 *
	 * @param string $status   Filter by status ('' = all).
	 * @param int    $page     1-based page.
	 * @param int    $per_page Page size.
	 * @return array{rows: array<int, object>, total: int}
	 */
	public function list( string $status = '', int $page = 1, int $per_page = 20 ): array {
		global $wpdb;

		$table  = Schema::table( 'inquiries' );
		$where  = '1=1';
		$params = array();

		if ( '' !== $status && in_array( $status, self::STATUSES, true ) ) {
			$where    = 'status = %s';
			$params[] = $status;
		}

		$offset   = ( max( 1, $page ) - 1 ) * $per_page;
		$params[] = $per_page;
		$params[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name + validated where clause.
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS * FROM {$table} WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d", $params ) );
		$total = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		return array(
			'rows'  => (array) $rows,
			'total' => $total,
		);
	}

	/**
	 * Move an inquiry through the pipeline.
	 *
	 * @param int    $id     Inquiry ID.
	 * @param string $status New status.
	 */
	public function set_status( int $id, string $status ): bool {
		global $wpdb;

		if ( ! in_array( $status, self::STATUSES, true ) ) {
			return false;
		}

		$updated = (bool) $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			Schema::table( 'inquiries' ),
			array( 'status' => $status ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( $updated ) {
			/**
			 * Fires when an inquiry changes status.
			 *
			 * @param int    $id     Inquiry ID.
			 * @param string $status New status.
			 */
			do_action( 'dmf/inquiries/status_changed', $id, $status );
		}

		return $updated;
	}
}
