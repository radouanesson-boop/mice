<?php
/**
 * Notifications module.
 *
 * @package DMF
 */

namespace DMF\Modules\Notifications;

use DMF\Container;
use DMF\Support\AbstractModule;
use DMF\Support\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Transactional email with a tiny mustache-style templating layer
 * ({{placeholders}}), filterable per template so destinations can rebrand
 * every message without touching code.
 */
class NotificationsModule extends AbstractModule {

	/**
	 * Settings accessor.
	 */
	private Options $options;

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'notifications';
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		$this->options = $container->get( Options::class );

		add_action( 'dmf/inquiries/created', array( $this, 'inquiry_notifications' ), 10, 2 );
	}

	/**
	 * Send a templated mail.
	 *
	 * @param string                $to           Recipient.
	 * @param string                $template     Template key, e.g. "inquiry_admin".
	 * @param string                $subject      Subject line (may contain placeholders).
	 * @param string                $body         Body (may contain placeholders).
	 * @param array<string, string> $placeholders Placeholder map without braces.
	 */
	public function send( string $to, string $template, string $subject, string $body, array $placeholders = array() ): bool {
		/**
		 * Filter subject/body per template before interpolation.
		 *
		 * @param array{subject: string, body: string} $message      Message parts.
		 * @param array<string, string>                $placeholders Placeholder map.
		 */
		$message = (array) apply_filters(
			"dmf/notifications/message/{$template}",
			array(
				'subject' => $subject,
				'body'    => $body,
			),
			$placeholders
		);

		$search  = array_map( static fn( string $key ): string => '{{' . $key . '}}', array_keys( $placeholders ) );
		$replace = array_values( array_map( 'strval', $placeholders ) );

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			sprintf(
				'From: %s <%s>',
				(string) $this->options->get( 'notifications.from_name', get_bloginfo( 'name' ) ),
				(string) $this->options->get( 'notifications.from_email', get_bloginfo( 'admin_email' ) )
			),
		);

		return wp_mail(
			$to,
			str_replace( $search, $replace, (string) ( $message['subject'] ?? $subject ) ),
			str_replace( $search, $replace, (string) ( $message['body'] ?? $body ) ),
			$headers
		);
	}

	/**
	 * Notify staff (and the listing's partner) about a new inquiry.
	 *
	 * @param int                  $id  Inquiry ID.
	 * @param array<string, mixed> $row Inquiry row.
	 */
	public function inquiry_notifications( int $id, array $row ): void {
		$listing_title = $row['listing_id'] ? get_the_title( (int) $row['listing_id'] ) : __( 'General inquiry', 'dmf' );

		$placeholders = array(
			'id'         => (string) $id,
			'listing'    => (string) $listing_title,
			'name'       => (string) $row['name'],
			'email'      => (string) $row['email'],
			'phone'      => (string) $row['phone'],
			'company'    => (string) $row['company'],
			'attendees'  => (string) $row['attendees'],
			'event_date' => (string) ( $row['event_date'] ?? '' ),
			'message'    => (string) $row['message'],
			'site'       => get_bloginfo( 'name' ),
		);

		$body = __( "New inquiry #{{id}} — {{listing}}\n\nFrom: {{name}} ({{company}})\nEmail: {{email}}\nPhone: {{phone}}\nAttendees: {{attendees}}\nEvent date: {{event_date}}\n\n{{message}}", 'dmf' );

		if ( $this->options->get( 'notifications.inquiry_admin', true ) ) {
			$this->send(
				(string) $this->options->get( 'notifications.from_email', get_bloginfo( 'admin_email' ) ),
				'inquiry_admin',
				/* translators: 1: listing title. */
				sprintf( __( '[{{site}}] New inquiry: %s', 'dmf' ), $listing_title ),
				$body,
				$placeholders
			);
		}

		if ( $this->options->get( 'notifications.inquiry_owner', true ) && $row['listing_id'] ) {
			$owner = get_userdata( (int) get_post_field( 'post_author', (int) $row['listing_id'] ) );
			if ( $owner && ! user_can( $owner, 'manage_dmf' ) ) {
				$this->send( $owner->user_email, 'inquiry_owner', __( '[{{site}}] You received a new inquiry', 'dmf' ), $body, $placeholders );
			}
		}
	}
}
