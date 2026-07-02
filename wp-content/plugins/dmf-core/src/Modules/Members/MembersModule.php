<?php
/**
 * Members module.
 *
 * @package DMF
 */

namespace DMF\Modules\Members;

use DMF\Container;
use DMF\Modules\Listings\PostTypes;
use DMF\Support\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * The member/partner layer: organization profile fields on the user,
 * partner verification, ownership helpers, and the frontend dashboard
 * rewrite (/account) rendered by the theme via the template engine.
 */
class MembersModule extends AbstractModule {

	public const QUERY_VAR = 'dmf_dashboard';

	/**
	 * Organization profile fields stored as user meta.
	 *
	 * @var array<string, string>
	 */
	private const PROFILE_FIELDS = array(
		'dmf_org_name'    => 'sanitize_text_field',
		'dmf_org_role'    => 'sanitize_text_field',
		'dmf_org_phone'   => 'sanitize_text_field',
		'dmf_org_website' => 'esc_url_raw',
		'dmf_org_country' => 'sanitize_text_field',
	);

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'members';
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		add_action( 'init', array( $this, 'add_rewrites' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_filter( 'template_include', array( $this, 'dashboard_template' ) );

		add_action( 'show_user_profile', array( $this, 'profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'profile_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_profile' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile' ) );
	}

	/**
	 * /account/{section} rewrite for the member dashboard.
	 */
	public function add_rewrites(): void {
		add_rewrite_rule( '^account/?$', 'index.php?' . self::QUERY_VAR . '=overview', 'top' );
		add_rewrite_rule( '^account/([^/]+)/?$', 'index.php?' . self::QUERY_VAR . '=$matches[1]', 'top' );
	}

	/**
	 * Register the dashboard query var.
	 *
	 * @param string[] $vars Query vars.
	 * @return string[]
	 */
	public function query_vars( array $vars ): array {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	/**
	 * Route /account to the dashboard template (theme-overridable).
	 *
	 * @param string $template Resolved template.
	 */
	public function dashboard_template( string $template ): string {
		$section = get_query_var( self::QUERY_VAR );

		if ( '' === $section ) {
			return $template;
		}

		$located = \DMF\Modules\Templates\TemplatesModule::locate( 'dashboard.php' );

		return $located ?: $template;
	}

	/**
	 * Dashboard sections — themes render navigation from this.
	 *
	 * @return array<string, string> slug => label.
	 */
	public static function sections(): array {
		/**
		 * Filter member dashboard sections.
		 *
		 * @param array<string, string> $sections slug => label.
		 */
		return (array) apply_filters(
			'dmf/members/sections',
			array(
				'overview'  => __( 'Overview', 'dmf' ),
				'listings'  => __( 'My Listings', 'dmf' ),
				'favorites' => __( 'Saved Listings', 'dmf' ),
				'inquiries' => __( 'Inquiries', 'dmf' ),
				'profile'   => __( 'Organization Profile', 'dmf' ),
			)
		);
	}

	/**
	 * Listings owned by a user.
	 *
	 * @param int $user_id User ID.
	 * @return int[]
	 */
	public static function owned_listings( int $user_id ): array {
		return array_map(
			'intval',
			get_posts(
				array(
					'post_type'      => PostTypes::LISTING,
					'post_status'    => array( 'publish', 'pending', 'draft' ),
					'author'         => $user_id,
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			)
		);
	}

	/**
	 * Whether a user is a verified partner.
	 *
	 * @param int $user_id User ID.
	 */
	public static function is_verified( int $user_id ): bool {
		return (bool) get_user_meta( $user_id, 'dmf_verified_partner', true );
	}

	/**
	 * Render organization fields on the user profile screen.
	 *
	 * @param \WP_User $user User being edited.
	 */
	public function profile_fields( \WP_User $user ): void {
		wp_nonce_field( 'dmf_save_profile', 'dmf_profile_nonce' );

		echo '<h2>' . esc_html__( 'Destination Partner Profile', 'dmf' ) . '</h2><table class="form-table" role="presentation">';

		$labels = array(
			'dmf_org_name'    => __( 'Organization', 'dmf' ),
			'dmf_org_role'    => __( 'Role / Job Title', 'dmf' ),
			'dmf_org_phone'   => __( 'Phone', 'dmf' ),
			'dmf_org_website' => __( 'Website', 'dmf' ),
			'dmf_org_country' => __( 'Country', 'dmf' ),
		);

		foreach ( $labels as $key => $label ) {
			printf(
				'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="text" class="regular-text" id="%1$s" name="%1$s" value="%3$s" /></td></tr>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_attr( (string) get_user_meta( $user->ID, $key, true ) )
			);
		}

		if ( current_user_can( 'manage_dmf' ) ) {
			printf(
				'<tr><th scope="row">%s</th><td><label><input type="checkbox" name="dmf_verified_partner" value="1" %s /> %s</label></td></tr>',
				esc_html__( 'Verification', 'dmf' ),
				checked( self::is_verified( $user->ID ), true, false ),
				esc_html__( 'Verified partner organization', 'dmf' )
			);
		}

		echo '</table>';
	}

	/**
	 * Persist organization fields.
	 *
	 * @param int $user_id User being saved.
	 */
	public function save_profile( int $user_id ): void {
		if ( ! isset( $_POST['dmf_profile_nonce'] ) || ! wp_verify_nonce( sanitize_key( (string) wp_unslash( $_POST['dmf_profile_nonce'] ) ), 'dmf_save_profile' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		foreach ( self::PROFILE_FIELDS as $key => $sanitize ) {
			$raw = isset( $_POST[ $key ] ) ? (string) wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized next line.
			update_user_meta( $user_id, $key, $sanitize( $raw ) );
		}

		if ( current_user_can( 'manage_dmf' ) ) {
			update_user_meta( $user_id, 'dmf_verified_partner', empty( $_POST['dmf_verified_partner'] ) ? 0 : 1 );
		}
	}
}
