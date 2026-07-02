<?php
/**
 * Favorites module.
 *
 * @package DMF
 */

namespace DMF\Modules\Favorites;

use DMF\Container;
use DMF\Modules\Installer\Schema;
use DMF\Support\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Saved listings ("shortlists"). Planners collect venues/hotels while
 * building an RFP; the dashboard and the inquiry form read the same list.
 */
class FavoritesModule extends AbstractModule {

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'favorites';
	}

	/**
	 * Toggle a favorite; returns the new state.
	 *
	 * @param int $user_id   User ID.
	 * @param int $object_id Listing ID.
	 */
	public function toggle( int $user_id, int $object_id ): bool {
		global $wpdb;

		$table = Schema::table( 'favorites' );

		if ( $this->has( $user_id, $object_id ) ) {
			$wpdb->delete( $table, array( 'user_id' => $user_id, 'object_id' => $object_id ), array( '%d', '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$state = false;
		} else {
			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$table,
				array(
					'user_id'    => $user_id,
					'object_id'  => $object_id,
					'created_at' => current_time( 'mysql', true ),
				),
				array( '%d', '%d', '%s' )
			);
			$state = true;
		}

		/**
		 * Fires when a favorite is toggled.
		 *
		 * @param int  $user_id   User ID.
		 * @param int  $object_id Listing ID.
		 * @param bool $state     New state.
		 */
		do_action( 'dmf/favorites/toggled', $user_id, $object_id, $state );

		return $state;
	}

	/**
	 * Whether a listing is favorited by a user.
	 *
	 * @param int $user_id   User ID.
	 * @param int $object_id Listing ID.
	 */
	public function has( int $user_id, int $object_id ): bool {
		global $wpdb;

		return (bool) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				'SELECT 1 FROM ' . Schema::table( 'favorites' ) . ' WHERE user_id = %d AND object_id = %d', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$user_id,
				$object_id
			)
		);
	}

	/**
	 * A user's favorite listing IDs, newest first.
	 *
	 * @param int $user_id User ID.
	 * @return int[]
	 */
	public function list( int $user_id ): array {
		global $wpdb;

		return array_map(
			'intval',
			$wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					'SELECT object_id FROM ' . Schema::table( 'favorites' ) . ' WHERE user_id = %d ORDER BY created_at DESC', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$user_id
				)
			)
		);
	}

	/**
	 * How many users saved a listing.
	 *
	 * @param int $object_id Listing ID.
	 */
	public function count( int $object_id ): int {
		global $wpdb;

		return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . Schema::table( 'favorites' ) . ' WHERE object_id = %d', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$object_id
			)
		);
	}
}
