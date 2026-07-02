<?php
/**
 * Roles and capabilities.
 *
 * @package DMF
 */

namespace DMF\Modules\Installer;

defined( 'ABSPATH' ) || exit;

/**
 * The framework maps the destination ecosystem onto WordPress roles:
 *
 *  - dmf_partner  a member organization (hotel, DMC, venue…) managing its
 *                 own listings, media and leads.
 *  - dmf_manager  bureau staff: full control of listings, events, inquiries
 *                 and members, without site administration.
 *
 * Listings use the `dmf_listing` capability type with map_meta_cap, so
 * partners can only ever edit their own content.
 */
class Roles {

	/**
	 * Capabilities granted to bureau staff and administrators.
	 *
	 * @return string[]
	 */
	public static function manager_caps(): array {
		return array(
			'read'                        => true,
			'upload_files'                => true,
			'manage_dmf'                  => true,
			'view_dmf_inquiries'          => true,
			'manage_dmf_inquiries'        => true,
			// Listing CPT (capability_type dmf_listing).
			'edit_dmf_listings'           => true,
			'edit_others_dmf_listings'    => true,
			'edit_private_dmf_listings'   => true,
			'edit_published_dmf_listings' => true,
			'publish_dmf_listings'        => true,
			'read_private_dmf_listings'   => true,
			'delete_dmf_listings'         => true,
			'delete_others_dmf_listings'  => true,
			'delete_private_dmf_listings' => true,
			'delete_published_dmf_listings' => true,
			// Event CPT (capability_type dmf_event).
			'edit_dmf_events'             => true,
			'edit_others_dmf_events'      => true,
			'edit_private_dmf_events'     => true,
			'edit_published_dmf_events'   => true,
			'publish_dmf_events'          => true,
			'read_private_dmf_events'     => true,
			'delete_dmf_events'           => true,
			'delete_others_dmf_events'    => true,
			'delete_private_dmf_events'   => true,
			'delete_published_dmf_events' => true,
		);
	}

	/**
	 * Capabilities granted to partner organizations (own content only).
	 *
	 * @return string[]
	 */
	public static function partner_caps(): array {
		return array(
			'read'                        => true,
			'upload_files'                => true,
			'view_dmf_inquiries'          => true,
			'edit_dmf_listings'           => true,
			'edit_published_dmf_listings' => true,
			'delete_dmf_listings'         => true,
		);
	}

	/**
	 * Create roles and grant capabilities to built-in roles.
	 */
	public static function install(): void {
		add_role( 'dmf_partner', __( 'Destination Partner', 'dmf' ), self::partner_caps() );
		add_role( 'dmf_manager', __( 'Destination Manager', 'dmf' ), self::manager_caps() );

		foreach ( array( 'administrator', 'editor' ) as $role_name ) {
			$role = get_role( $role_name );
			if ( ! $role ) {
				continue;
			}
			foreach ( array_keys( self::manager_caps() ) as $cap ) {
				$role->add_cap( $cap );
			}
		}
	}

	/**
	 * Remove framework roles/caps (uninstall only — deactivation keeps data).
	 */
	public static function uninstall(): void {
		remove_role( 'dmf_partner' );
		remove_role( 'dmf_manager' );

		foreach ( array( 'administrator', 'editor' ) as $role_name ) {
			$role = get_role( $role_name );
			if ( ! $role ) {
				continue;
			}
			foreach ( array_keys( self::manager_caps() ) as $cap ) {
				$role->remove_cap( $cap );
			}
		}
	}
}
