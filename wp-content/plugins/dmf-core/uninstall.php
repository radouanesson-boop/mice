<?php
/**
 * Uninstall — removes framework data ONLY when the site owner opts in by
 * setting DMF_REMOVE_DATA_ON_UNINSTALL to true in wp-config.php. Content
 * (listings, inquiries) is never deleted silently.
 *
 * @package DMF
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'DMF_REMOVE_DATA_ON_UNINSTALL' ) || ! DMF_REMOVE_DATA_ON_UNINSTALL ) {
	return;
}

delete_option( 'dmf_settings' );
delete_option( 'dmf_listing_types' );
delete_option( 'dmf_version' );

remove_role( 'dmf_partner' );
