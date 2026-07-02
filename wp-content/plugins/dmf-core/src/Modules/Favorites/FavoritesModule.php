<?php
/**
 * Favorites module — saved listings per user.
 *
 * @package DMF
 */

namespace DMF\Modules\Favorites;

use DMF\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * User bookmarks stored in user meta, exposed over authenticated REST.
 *
 *   GET    /dmf/v1/favorites          current user's saved listing ids
 *   POST   /dmf/v1/favorites/{id}     toggle a listing
 */
final class FavoritesModule extends AbstractModule {

	private const META = 'dmf_favorites';

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'routes' ) );
	}

	public function routes(): void {
		register_rest_route(
			'dmf/v1',
			'/favorites',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => fn() => rest_ensure_response( array( 'ids' => $this->ids( get_current_user_id() ) ) ),
				'permission_callback' => fn() => is_user_logged_in(),
			)
		);

		register_rest_route(
			'dmf/v1',
			'/favorites/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'toggle' ),
				'permission_callback' => fn() => is_user_logged_in(),
				'args'                => array(
					'id' => array( 'type' => 'integer', 'required' => true ),
				),
			)
		);
	}

	public function toggle( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$post_id = (int) $request['id'];
		$post    = get_post( $post_id );
		$types   = dmf()->module( 'listings' )->registry()->post_types();

		if ( ! $post || 'publish' !== $post->post_status || ! in_array( $post->post_type, $types, true ) ) {
			return new \WP_Error( 'dmf_invalid_listing', __( 'Not a listing.', 'dmf' ), array( 'status' => 404 ) );
		}

		$user_id = get_current_user_id();
		$ids     = $this->ids( $user_id );
		$saved   = in_array( $post_id, $ids, true );

		$ids = $saved
			? array_values( array_diff( $ids, array( $post_id ) ) )
			: array_merge( $ids, array( $post_id ) );

		update_user_meta( $user_id, self::META, $ids );

		/**
		 * Fires when a favorite is toggled.
		 *
		 * @param int  $post_id Listing.
		 * @param int  $user_id User.
		 * @param bool $added   True when added, false when removed.
		 */
		do_action( 'dmf/favorite_toggled', $post_id, $user_id, ! $saved );

		return rest_ensure_response(
			array(
				'id'    => $post_id,
				'saved' => ! $saved,
				'count' => count( $ids ),
			)
		);
	}

	/**
	 * @return int[]
	 */
	public function ids( int $user_id ): array {
		return array_values( array_filter( array_map( 'absint', (array) get_user_meta( $user_id, self::META, true ) ) ) );
	}
}
