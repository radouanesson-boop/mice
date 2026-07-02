<?php
/**
 * Listing repository.
 *
 * @package DMF
 */

namespace DMF\Modules\Listings;

use DMF\Support\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Friendly query surface over WP_Query for listings; used by blocks,
 * shortcodes, templates and REST so sorting/filtering semantics are defined
 * exactly once.
 */
class Repository {

	/**
	 * Constructor.
	 *
	 * @param Options $options Settings accessor.
	 */
	public function __construct( private Options $options ) {}

	/**
	 * Query listings.
	 *
	 * @param array<string, mixed> $args {
	 *     @type string|string[] $type     Listing type slug(s).
	 *     @type string          $category Category slug.
	 *     @type string          $tag      Tag slug.
	 *     @type bool            $featured Only featured listings.
	 *     @type bool            $verified Only verified listings.
	 *     @type string          $search   Keyword.
	 *     @type string          $sort     featured|newest|title|rating.
	 *     @type int             $per_page Page size.
	 *     @type int             $page     Page number.
	 *     @type int[]           $include  Restrict to these IDs (keeps order).
	 * }
	 */
	public function query( array $args = array() ): \WP_Query {
		$args = wp_parse_args(
			$args,
			array(
				'type'     => '',
				'category' => '',
				'tag'      => '',
				'featured' => false,
				'verified' => false,
				'search'   => '',
				'sort'     => (string) $this->options->get( 'listings.default_sort', 'featured' ),
				'per_page' => (int) $this->options->get( 'listings.per_page', 12 ),
				'page'     => 1,
				'include'  => array(),
			)
		);

		$query_args = array(
			'post_type'      => PostTypes::LISTING,
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, min( 100, (int) $args['per_page'] ) ),
			'paged'          => max( 1, (int) $args['page'] ),
			'tax_query'      => array(),
			'meta_query'     => array(),
		);

		if ( $args['include'] ) {
			$query_args['post__in'] = array_map( 'absint', (array) $args['include'] );
			$query_args['orderby']  = 'post__in';
		}

		if ( $args['search'] ) {
			$query_args['s'] = sanitize_text_field( (string) $args['search'] );
		}

		foreach ( array(
			'type'     => PostTypes::TYPE_TAXONOMY,
			'category' => PostTypes::CATEGORY,
			'tag'      => PostTypes::TAG,
		) as $arg => $taxonomy ) {
			if ( ! empty( $args[ $arg ] ) ) {
				$query_args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => array_map( 'sanitize_title', (array) $args[ $arg ] ),
				);
			}
		}

		foreach ( array( 'featured', 'verified' ) as $flag ) {
			if ( $args[ $flag ] ) {
				$query_args['meta_query'][] = array(
					'key'   => "_dmf_{$flag}",
					'value' => '1',
				);
			}
		}

		if ( empty( $query_args['orderby'] ) ) {
			switch ( (string) $args['sort'] ) {
				case 'title':
					$query_args['orderby'] = 'title';
					$query_args['order']   = 'ASC';
					break;
				case 'newest':
					$query_args['orderby'] = 'date';
					$query_args['order']   = 'DESC';
					break;
				case 'rating':
					$query_args['meta_key'] = '_dmf_rating';
					$query_args['orderby']  = array(
						'meta_value_num' => 'DESC',
						'date'           => 'DESC',
					);
					break;
				case 'featured':
				default:
					// Featured first, then freshest. The flag meta is
					// guaranteed to exist ('1'/'0') by ListingsModule.
					$query_args['meta_query']['featured_clause'] = array(
						'key'     => '_dmf_featured',
						'compare' => 'EXISTS',
						'type'    => 'NUMERIC',
					);
					$query_args['orderby'] = array(
						'featured_clause' => 'DESC',
						'date'            => 'DESC',
					);
					break;
			}
		}

		/**
		 * Filter listing query arguments before execution.
		 *
		 * @param array<string, mixed> $query_args WP_Query args.
		 * @param array<string, mixed> $args       Normalized repository args.
		 */
		$query_args = (array) apply_filters( 'dmf/listings/query_args', $query_args, $args );

		return new \WP_Query( $query_args );
	}
}
