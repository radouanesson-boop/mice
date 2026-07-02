<?php
/**
 * Database schema.
 *
 * @package DMF
 */

namespace DMF\Modules\Installer;

defined( 'ABSPATH' ) || exit;

/**
 * Custom tables. Listings, events and members ride on core WP tables
 * (posts/meta/users/comments); custom tables exist only where core storage
 * cannot deliver the required query performance:
 *
 *  - dmf_index      denormalized listing search index (facets, geo, rating)
 *  - dmf_favorites  user ↔ listing bookmarks
 *  - dmf_inquiries  RFP / lead pipeline
 *  - dmf_search_log saved search analytics
 */
class Schema {

	public const DB_VERSION = '1.0.0';

	/**
	 * Fully-prefixed table name.
	 *
	 * @param string $table Bare table name, e.g. "index".
	 */
	public static function table( string $table ): string {
		global $wpdb;
		return $wpdb->prefix . 'dmf_' . $table;
	}

	/**
	 * Create/upgrade all tables via dbDelta.
	 */
	public static function migrate(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();

		$index      = self::table( 'index' );
		$favorites  = self::table( 'favorites' );
		$inquiries  = self::table( 'inquiries' );
		$search_log = self::table( 'search_log' );

		dbDelta(
			array(
				"CREATE TABLE {$index} (
					listing_id BIGINT UNSIGNED NOT NULL,
					listing_type VARCHAR(64) NOT NULL DEFAULT '',
					status VARCHAR(20) NOT NULL DEFAULT 'publish',
					lat DECIMAL(10,7) DEFAULT NULL,
					lng DECIMAL(10,7) DEFAULT NULL,
					city VARCHAR(96) NOT NULL DEFAULT '',
					capacity INT UNSIGNED NOT NULL DEFAULT 0,
					price_level TINYINT UNSIGNED NOT NULL DEFAULT 0,
					rating DECIMAL(3,2) NOT NULL DEFAULT 0,
					review_count INT UNSIGNED NOT NULL DEFAULT 0,
					featured TINYINT(1) NOT NULL DEFAULT 0,
					verified TINYINT(1) NOT NULL DEFAULT 0,
					facets LONGTEXT NULL,
					search_text LONGTEXT NULL,
					updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY  (listing_id),
					KEY listing_type (listing_type),
					KEY geo (lat,lng),
					KEY featured (featured),
					KEY rating (rating)
				) {$charset};",

				"CREATE TABLE {$favorites} (
					id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
					user_id BIGINT UNSIGNED NOT NULL,
					object_id BIGINT UNSIGNED NOT NULL,
					created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY  (id),
					UNIQUE KEY user_object (user_id,object_id),
					KEY object_id (object_id)
				) {$charset};",

				"CREATE TABLE {$inquiries} (
					id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
					listing_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
					user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
					name VARCHAR(191) NOT NULL DEFAULT '',
					email VARCHAR(191) NOT NULL DEFAULT '',
					phone VARCHAR(64) NOT NULL DEFAULT '',
					company VARCHAR(191) NOT NULL DEFAULT '',
					country VARCHAR(2) NOT NULL DEFAULT '',
					event_date DATE DEFAULT NULL,
					attendees INT UNSIGNED NOT NULL DEFAULT 0,
					message TEXT NULL,
					status VARCHAR(20) NOT NULL DEFAULT 'new',
					source VARCHAR(64) NOT NULL DEFAULT 'web',
					created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY  (id),
					KEY listing_id (listing_id),
					KEY status (status),
					KEY created_at (created_at)
				) {$charset};",

				"CREATE TABLE {$search_log} (
					id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
					phrase VARCHAR(191) NOT NULL DEFAULT '',
					filters TEXT NULL,
					results INT UNSIGNED NOT NULL DEFAULT 0,
					user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
					created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY  (id),
					KEY phrase (phrase),
					KEY created_at (created_at)
				) {$charset};",
			)
		);

		update_option( 'dmf_db_version', self::DB_VERSION );
	}
}
