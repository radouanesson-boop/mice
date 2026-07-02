<?php
/**
 * Members module — partner accounts & front dashboard data.
 *
 * @package DMF
 */

namespace DMF\Modules\Members;

use DMF\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Extends users with an organization profile and provides the REST data
 * behind the front-end member dashboard (own listings, favorites,
 * inquiries about own listings). Membership plans/verification are
 * roadmap add-ons that hook `dmf/member_profile`.
 */
final class MembersModule extends AbstractModule {

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'routes' ) );

		// Organization profile fields on the user screen.
		add_action( 'show_user_profile', array( $this, 'profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'profile_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_profile' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile' ) );
	}

	public function routes(): void {
		register_rest_route(
			'dmf/v1',
			'/me',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'me' ),
				'permission_callback' => fn() => is_user_logged_in(),
			)
		);
	}

	/**
	 * The member dashboard payload.
	 */
	public function me(): \WP_REST_Response {
		$user_id = get_current_user_id();
		$types   = dmf()->module( 'listings' )->registry()->post_types();

		$own = get_posts(
			array(
				'post_type'      => $types,
				'author'         => $user_id,
				'post_status'    => array( 'publish', 'pending', 'draft' ),
				'posts_per_page' => 100,
				'fields'         => 'ids',
			)
		);

		$profile = array(
			'id'           => $user_id,
			'name'         => wp_get_current_user()->display_name,
			'organization' => get_user_meta( $user_id, 'dmf_organization', true ),
			'role'         => get_user_meta( $user_id, 'dmf_org_role', true ),
			'listings'     => array_map(
				static fn( int $id ) => array(
					'id'     => $id,
					'title'  => get_the_title( $id ),
					'status' => get_post_status( $id ),
					'url'    => get_permalink( $id ),
				),
				$own
			),
			'favorites'    => dmf()->module( 'favorites' )->ids( $user_id ),
		);

		/**
		 * Filter the member dashboard payload.
		 *
		 * @param array $profile Payload.
		 * @param int   $user_id User.
		 */
		return rest_ensure_response( apply_filters( 'dmf/member_profile', $profile, $user_id ) );
	}

	public function profile_fields( \WP_User $user ): void {
		wp_nonce_field( 'dmf_member', 'dmf_member_nonce' );
		?>
		<h2><?php esc_html_e( 'Destination profile', 'dmf' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="dmf_organization"><?php esc_html_e( 'Organization', 'dmf' ); ?></label></th>
				<td><input type="text" class="regular-text" id="dmf_organization" name="dmf_organization"
					value="<?php echo esc_attr( get_user_meta( $user->ID, 'dmf_organization', true ) ); ?>"></td>
			</tr>
			<tr>
				<th><label for="dmf_org_role"><?php esc_html_e( 'Role / title', 'dmf' ); ?></label></th>
				<td><input type="text" class="regular-text" id="dmf_org_role" name="dmf_org_role"
					value="<?php echo esc_attr( get_user_meta( $user->ID, 'dmf_org_role', true ) ); ?>"></td>
			</tr>
		</table>
		<?php
	}

	public function save_profile( int $user_id ): void {
		if (
			! isset( $_POST['dmf_member_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['dmf_member_nonce'] ), 'dmf_member' )
			|| ! current_user_can( 'edit_user', $user_id )
		) {
			return;
		}

		update_user_meta( $user_id, 'dmf_organization', sanitize_text_field( wp_unslash( $_POST['dmf_organization'] ?? '' ) ) );
		update_user_meta( $user_id, 'dmf_org_role', sanitize_text_field( wp_unslash( $_POST['dmf_org_role'] ?? '' ) ) );
	}
}
