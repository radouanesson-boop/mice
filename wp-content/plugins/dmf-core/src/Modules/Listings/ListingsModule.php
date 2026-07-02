<?php
/**
 * Listings module.
 *
 * @package DMF
 */

namespace DMF\Modules\Listings;

use DMF\Container;
use DMF\Support\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the listing content model and its admin refinements.
 */
class ListingsModule extends AbstractModule {

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'listings';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register( Container $container ): void {
		$container->singleton( PostTypes::class );
		$container->singleton( Repository::class );
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		add_action( 'init', array( $container->get( PostTypes::class ), 'register' ), 5 );
		add_action( 'save_post_' . PostTypes::LISTING, array( $this, 'ensure_flag_meta' ) );

		if ( is_admin() ) {
			add_filter( 'manage_' . PostTypes::LISTING . '_posts_columns', array( $this, 'columns' ) );
			add_action( 'manage_' . PostTypes::LISTING . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
		}
	}

	/**
	 * Guarantee the featured/verified flags exist as real meta rows so
	 * meta-based ordering (EXISTS clause) never drops listings.
	 *
	 * @param int $post_id Listing ID.
	 */
	public function ensure_flag_meta( int $post_id ): void {
		foreach ( array( '_dmf_featured', '_dmf_verified' ) as $key ) {
			if ( '' === (string) get_post_meta( $post_id, $key, true ) ) {
				update_post_meta( $post_id, $key, '0' );
			}
		}
	}

	/**
	 * Add Featured/Verified columns to the listing table.
	 *
	 * @param array<string, string> $columns Admin columns.
	 * @return array<string, string>
	 */
	public function columns( array $columns ): array {
		$columns['dmf_flags'] = __( 'Status', 'dmf' );
		return $columns;
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Listing ID.
	 */
	public function column_content( string $column, int $post_id ): void {
		if ( 'dmf_flags' !== $column ) {
			return;
		}

		$flags = array();
		if ( get_post_meta( $post_id, '_dmf_featured', true ) ) {
			$flags[] = esc_html__( 'Featured', 'dmf' );
		}
		if ( get_post_meta( $post_id, '_dmf_verified', true ) ) {
			$flags[] = esc_html__( 'Verified', 'dmf' );
		}

		echo esc_html( $flags ? implode( ' · ', $flags ) : '—' );
	}
}
