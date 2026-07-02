<?php
/**
 * Reviews module.
 *
 * @package DMF
 */

namespace DMF\Modules\Reviews;

use DMF\Container;
use DMF\Modules\Listings\PostTypes;
use DMF\Support\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Reviews ride on core comments (comment_type `dmf_review`) with a 1–5
 * rating in comment meta — moderation, spam protection, notifications and
 * GDPR export all come from WordPress. Aggregates are denormalized onto the
 * listing (`_dmf_rating`, `_dmf_review_count`) and flow into the search
 * index for rating filters/sorting.
 */
class ReviewsModule extends AbstractModule {

	public const COMMENT_TYPE = 'dmf_review';
	public const RATING_META  = 'dmf_rating';

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'reviews';
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		add_action( 'wp_insert_comment', array( $this, 'maybe_recount' ), 10, 2 );
		add_action( 'transition_comment_status', array( $this, 'recount_on_transition' ), 10, 3 );
	}

	/**
	 * Create a review (pending moderation by default).
	 *
	 * @param int    $listing_id Listing ID.
	 * @param int    $user_id    Author user ID (0 = guest).
	 * @param int    $rating     1–5.
	 * @param string $content    Review text.
	 * @param string $author     Guest author name.
	 * @param string $email      Guest author email.
	 * @return int|\WP_Error Comment ID.
	 */
	public function create( int $listing_id, int $user_id, int $rating, string $content, string $author = '', string $email = '' ): int|\WP_Error {
		if ( PostTypes::LISTING !== get_post_type( $listing_id ) ) {
			return new \WP_Error( 'dmf_invalid_listing', __( 'Reviews can only be left on listings.', 'dmf' ) );
		}

		$rating = max( 1, min( 5, $rating ) );

		$data = array(
			'comment_post_ID'      => $listing_id,
			'comment_content'      => sanitize_textarea_field( $content ),
			'comment_type'         => self::COMMENT_TYPE,
			'user_id'              => $user_id,
			'comment_author'       => sanitize_text_field( $author ),
			'comment_author_email' => sanitize_email( $email ),
			'comment_approved'     => 0,
			'comment_meta'         => array( self::RATING_META => $rating ),
		);

		if ( $user_id ) {
			$user                         = get_userdata( $user_id );
			$data['comment_author']       = $user ? $user->display_name : $data['comment_author'];
			$data['comment_author_email'] = $user ? $user->user_email : $data['comment_author_email'];
		}

		$comment_id = wp_new_comment( $data, true );

		if ( is_wp_error( $comment_id ) ) {
			return $comment_id;
		}

		/**
		 * Fires when a review is submitted.
		 *
		 * @param int $comment_id Review comment ID.
		 * @param int $listing_id Listing ID.
		 * @param int $rating     Rating value.
		 */
		do_action( 'dmf/reviews/created', (int) $comment_id, $listing_id, $rating );

		return (int) $comment_id;
	}

	/**
	 * Approved reviews for a listing.
	 *
	 * @param int $listing_id Listing ID.
	 * @return \WP_Comment[]
	 */
	public function for_listing( int $listing_id ): array {
		return get_comments(
			array(
				'post_id' => $listing_id,
				'type'    => self::COMMENT_TYPE,
				'status'  => 'approve',
			)
		);
	}

	/**
	 * Recount when a fresh review arrives approved.
	 *
	 * @param int         $comment_id Comment ID.
	 * @param \WP_Comment $comment    Comment object.
	 */
	public function maybe_recount( int $comment_id, \WP_Comment $comment ): void {
		if ( self::COMMENT_TYPE === $comment->comment_type && 1 === (int) $comment->comment_approved ) {
			$this->recount( (int) $comment->comment_post_ID );
		}
	}

	/**
	 * Recount when moderation status changes.
	 *
	 * @param string      $new     New status.
	 * @param string      $old     Old status.
	 * @param \WP_Comment $comment Comment object.
	 */
	public function recount_on_transition( string $new, string $old, \WP_Comment $comment ): void {
		if ( self::COMMENT_TYPE === $comment->comment_type && $new !== $old ) {
			$this->recount( (int) $comment->comment_post_ID );
		}
	}

	/**
	 * Recompute and store a listing's aggregate rating.
	 *
	 * @param int $listing_id Listing ID.
	 */
	public function recount( int $listing_id ): void {
		$reviews = $this->for_listing( $listing_id );
		$count   = count( $reviews );
		$sum     = 0;

		foreach ( $reviews as $review ) {
			$sum += (int) get_comment_meta( (int) $review->comment_ID, self::RATING_META, true );
		}

		$rating = $count ? round( $sum / $count, 2 ) : 0;

		update_post_meta( $listing_id, '_dmf_rating', $rating );
		update_post_meta( $listing_id, '_dmf_review_count', $count );

		/**
		 * Fires after a listing's rating aggregate is refreshed. The search
		 * module listens to reindex the listing.
		 *
		 * @param int   $listing_id Listing ID.
		 * @param float $rating     New average rating.
		 * @param int   $count      Approved review count.
		 */
		do_action( 'dmf/reviews/recounted', $listing_id, (float) $rating, $count );
	}
}
