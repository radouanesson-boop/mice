<?php
/**
 * Block: dmf/inquiry-form — RFP / inquiry form posting to /dmf/v1/inquiries.
 *
 * Progressive enhancement: with JS the theme submits over REST and shows
 * inline feedback; without JS it degrades to a normal POST handled by the
 * same endpoint via the REST form-encoding bridge.
 *
 * @var array $args { listingId }
 * @package DMF
 */

defined( 'ABSPATH' ) || exit;

$dmf_listing_id = (int) ( $args['listingId'] ?? 0 );
?>
<form class="dmf-inquiry" data-dmf-inquiry
	data-endpoint="<?php echo esc_url( rest_url( 'dmf/v1/inquiries' ) ); ?>"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'dmf_inquiry' ) ); ?>"
	method="post" action="<?php echo esc_url( rest_url( 'dmf/v1/inquiries' ) ); ?>">

	<h2 class="dmf-inquiry__title"><?php esc_html_e( 'Plan your event', 'dmf' ); ?></h2>

	<input type="hidden" name="listing_id" value="<?php echo esc_attr( (string) $dmf_listing_id ); ?>">
	<p class="dmf-inquiry__hp" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>

	<p>
		<label for="dmf-inq-name"><?php esc_html_e( 'Name', 'dmf' ); ?> *</label>
		<input id="dmf-inq-name" type="text" name="name" required autocomplete="name">
	</p>
	<p>
		<label for="dmf-inq-email"><?php esc_html_e( 'Email', 'dmf' ); ?> *</label>
		<input id="dmf-inq-email" type="email" name="email" required autocomplete="email">
	</p>
	<p>
		<label for="dmf-inq-company"><?php esc_html_e( 'Company / organization', 'dmf' ); ?></label>
		<input id="dmf-inq-company" type="text" name="company" autocomplete="organization">
	</p>
	<p>
		<label for="dmf-inq-type"><?php esc_html_e( 'Event type', 'dmf' ); ?></label>
		<select id="dmf-inq-type" name="event_type">
			<option value=""><?php esc_html_e( '— Select —', 'dmf' ); ?></option>
			<option value="congress"><?php esc_html_e( 'Congress', 'dmf' ); ?></option>
			<option value="conference"><?php esc_html_e( 'Conference / meeting', 'dmf' ); ?></option>
			<option value="incentive"><?php esc_html_e( 'Incentive', 'dmf' ); ?></option>
			<option value="exhibition"><?php esc_html_e( 'Exhibition / trade show', 'dmf' ); ?></option>
			<option value="gala"><?php esc_html_e( 'Gala / special event', 'dmf' ); ?></option>
		</select>
	</p>
	<p>
		<label for="dmf-inq-delegates"><?php esc_html_e( 'Delegates', 'dmf' ); ?></label>
		<input id="dmf-inq-delegates" type="text" name="delegates" inputmode="numeric" placeholder="500 – 2,000">
	</p>
	<p>
		<label for="dmf-inq-dates"><?php esc_html_e( 'Preferred dates', 'dmf' ); ?></label>
		<input id="dmf-inq-dates" type="text" name="dates" placeholder="Q2 2027">
	</p>
	<p>
		<label for="dmf-inq-message"><?php esc_html_e( 'Your project', 'dmf' ); ?></label>
		<textarea id="dmf-inq-message" name="message" rows="5"></textarea>
	</p>

	<p class="dmf-inquiry__actions">
		<button type="submit" class="dmf-inquiry__submit"><?php esc_html_e( 'Submit an RFP', 'dmf' ); ?></button>
	</p>

	<p class="dmf-inquiry__feedback" data-dmf-inquiry-feedback role="status" aria-live="polite"></p>
</form>
