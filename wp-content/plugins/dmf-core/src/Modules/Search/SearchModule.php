<?php
/**
 * Search module.
 *
 * @package DMF
 */

namespace DMF\Modules\Search;

use DMF\Container;
use DMF\Modules\Installer\Schema;
use DMF\Modules\Listings\PostTypes;
use DMF\Support\AbstractModule;
use DMF\Support\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Wires the indexer to content lifecycle events, keeps the index fresh via
 * daily cron, and logs search phrases for the analytics dashboard.
 */
class SearchModule extends AbstractModule {

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'search';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register( Container $container ): void {
		$container->singleton( Indexer::class );
		$container->singleton( SearchQuery::class );
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		$indexer = $container->get( Indexer::class );

		// Fields are saved after the post — index once, at the end.
		add_action( 'dmf/fields/saved', static fn( int $post_id ) => $indexer->index( $post_id ), 99 );
		add_action( 'save_post_' . PostTypes::LISTING, static fn( int $post_id ) => $indexer->index( $post_id ), 99 );
		add_action( 'before_delete_post', static fn( int $post_id ) => $indexer->remove( $post_id ) );
		add_action(
			'transition_post_status',
			static function ( string $new, string $old, \WP_Post $post ) use ( $indexer ) {
				if ( PostTypes::LISTING === $post->post_type && 'publish' !== $new ) {
					$indexer->remove( $post->ID );
				}
			},
			10,
			3
		);

		// Rating aggregates changed → refresh the listing's index row.
		add_action( 'dmf/reviews/recounted', static fn( int $post_id ) => $indexer->index( $post_id ) );

		add_action( 'dmf/cron/reindex', static fn() => $indexer->rebuild() );

		if ( $container->get( Options::class )->get( 'search.log_searches', true ) ) {
			add_action( 'dmf/search/executed', array( $this, 'log_search' ), 10, 3 );
		}
	}

	/**
	 * Persist a search-log row (fed to admin analytics + saved searches).
	 *
	 * @param array<string, mixed> $args  Search args.
	 * @param int[]                $ids   Result IDs.
	 * @param int                  $total Total results.
	 */
	public function log_search( array $args, array $ids, int $total ): void {
		global $wpdb;

		$phrase = trim( (string) ( $args['keyword'] ?? '' ) );
		if ( '' === $phrase ) {
			return;
		}

		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			Schema::table( 'search_log' ),
			array(
				'phrase'     => mb_substr( $phrase, 0, 191 ),
				'filters'    => (string) wp_json_encode( array_diff_key( $args, array( 'keyword' => 1, 'page' => 1, 'per_page' => 1 ) ) ),
				'results'    => $total,
				'user_id'    => get_current_user_id(),
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%d', '%d', '%s' )
		);
	}
}
