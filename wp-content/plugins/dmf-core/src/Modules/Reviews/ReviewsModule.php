<?php
/**
 * Reviews module — ratings on listings via native comments.
 *
 * @package DMF
 */

namespace DMF\Modules\Reviews;

use DMF\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Reviews reuse the battle-tested comment pipeline (moderation, spam
 * plugins, notifications) with a `dmf_review` comment type carrying a
 * 1–5 rating in comment meta. The listing's average is denormalized to
 * `_dmf_rating` / `_dmf_rating_count` post meta so cards and sorting stay
 * one cheap meta read.
 */
final class ReviewsModule extends AbstractModule {

	public const TYPE = 'dmf_review';

	public function register(): void {
		add_action( 'comment_post', array( $this, 'save_rating' ), 10, 3 );
		add_action( 'wp_set_comment_status', array( $this, 'recount_on_status' ), 10, 2 );
	}

	/**
	 * Attach the submitted rating to a new review and recount.
	 *
	 * @param int        $comment_id New comment.
	 * @param int|string $approved   Approval status.
	 * @param array      $data       Comment data.
	 */
	public function save_rating( int $comment_id, $approved, array $data ): void {
		if ( ! isset( $_POST['dmf_rating'], $_POST['dmf_review_nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified next line.
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['dmf_review_nonce'] ), 'dmf_review' ) ) {
			return;
		}

		$rating = max( 1, min( 5, absint( $_POST['dmf_rating'] ) ) );

		wp_update_comment( array( 'comment_ID' => $comment_id, 'comment_type' => self::TYPE ) );
		update_comment_meta( $comment_id, 'dmf_rating', $rating );

		$this->recount( (int) ( $data['comment_post_ID'] ?? 0 ) );
	}

	/**
	 * Keep averages honest when moderation changes a review's status.
	 *
	 * @param int|string $comment_id Comment.
	 * @param string     $status     New status.
	 */
	public function recount_on_status( $comment_id, $status ): void {
		$comment = get_comment( $comment_id );

		if ( $comment && self::TYPE === $comment->comment_type ) {
			$this->recount( (int) $comment->comment_post_ID );
		}
	}

	/**
	 * Recompute the denormalized average for a listing.
	 */
	public function recount( int $post_id ): void {
		if ( ! $post_id ) {
			return;
		}

		$comments = get_comments(
			array(
				'post_id' => $post_id,
				'type'    => self::TYPE,
				'status'  => 'approve',
			)
		);

		$ratings = array_filter(
			array_map(
				static fn( $c ) => (float) get_comment_meta( $c->comment_ID, 'dmf_rating', true ),
				$comments
			)
		);

		if ( empty( $ratings ) ) {
			delete_post_meta( $post_id, '_dmf_rating' );
			delete_post_meta( $post_id, '_dmf_rating_count' );
			return;
		}

		update_post_meta( $post_id, '_dmf_rating', round( array_sum( $ratings ) / count( $ratings ), 1 ) );
		update_post_meta( $post_id, '_dmf_rating_count', count( $ratings ) );
	}
}
