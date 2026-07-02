<?php
/**
 * DMF template API — the public helper functions themes build on.
 *
 * Everything here is pluggable (function_exists guards): a theme or
 * extension can redefine any helper before plugins_loaded completes.
 *
 * @package DMF
 */

defined( 'ABSPATH' ) || exit;

use DMF\Modules\Favorites\FavoritesModule;
use DMF\Modules\Fields\FieldRegistry;
use DMF\Modules\Listings\PostTypes;
use DMF\Modules\Listings\Repository;
use DMF\Modules\ListingTypes\TypeRegistry;
use DMF\Modules\Templates\TemplatesModule;

if ( ! function_exists( 'dmf_part' ) ) {
	/**
	 * Render a template part. Looks in the theme's template-parts/ first
	 * (child, then parent), then the plugin's templates/parts/ directory.
	 *
	 * @param string               $slug Path without extension, e.g. "cards/card-venue".
	 * @param array<string, mixed> $args Data exposed as $args inside the part.
	 */
	function dmf_part( string $slug, array $args = array() ): void {
		if ( locate_template( 'template-parts/' . $slug . '.php' ) ) {
			get_template_part( 'template-parts/' . $slug, null, $args );
			return;
		}

		TemplatesModule::render( 'parts/' . $slug . '.php', $args );
	}
}

if ( ! function_exists( 'dmf_part_html' ) ) {
	/**
	 * Return a template part as a string (blocks, shortcodes, emails).
	 *
	 * @param string               $slug Part path.
	 * @param array<string, mixed> $args Part data.
	 */
	function dmf_part_html( string $slug, array $args = array() ): string {
		ob_start();
		dmf_part( $slug, $args );
		return (string) ob_get_clean();
	}
}

if ( ! function_exists( 'dmf_bem' ) ) {
	/**
	 * Build a BEM class attribute: dmf_bem( 'dmf-card', [ 'venue' ] ) →
	 * "dmf-card dmf-card--venue".
	 *
	 * @param string                   $block     Block name.
	 * @param array<int, string|null>  $modifiers Modifiers (empties skipped).
	 */
	function dmf_bem( string $block, array $modifiers = array() ): string {
		$classes = array( $block );
		foreach ( array_filter( $modifiers ) as $modifier ) {
			$classes[] = $block . '--' . $modifier;
		}
		return esc_attr( implode( ' ', $classes ) );
	}
}

if ( ! function_exists( 'dmf_icon' ) ) {
	/**
	 * Inline SVG icon. Resolution: child theme assets/svg → parent theme
	 * assets/svg → plugin assets/svg. Inlined so icons inherit currentColor.
	 *
	 * @param string               $name Icon file name without extension.
	 * @param array<string, mixed> $args { echo?: bool, class?: string, label?: string }.
	 * @return string|void
	 */
	function dmf_icon( string $name, array $args = array() ) {
		$args = wp_parse_args( $args, array( 'echo' => true, 'class' => '', 'label' => '' ) );

		static $cache = array();

		if ( ! isset( $cache[ $name ] ) ) {
			$file = sanitize_file_name( $name ) . '.svg';
			$candidates = array(
				get_stylesheet_directory() . '/assets/svg/' . $file,
				get_template_directory() . '/assets/svg/' . $file,
				DMF_DIR . 'assets/svg/' . $file,
			);

			$cache[ $name ] = '';
			foreach ( $candidates as $path ) {
				if ( file_exists( $path ) ) {
					$cache[ $name ] = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
					break;
				}
			}
		}

		if ( '' === $cache[ $name ] ) {
			return $args['echo'] ? null : '';
		}

		$class = trim( 'dmf-icon dmf-icon--' . sanitize_html_class( $name ) . ' ' . (string) $args['class'] );
		$a11y  = '' === $args['label']
			? 'aria-hidden="true" focusable="false"'
			: 'role="img" aria-label="' . esc_attr( (string) $args['label'] ) . '"';

		$svg = (string) preg_replace( '/<svg\b/', '<svg class="' . esc_attr( $class ) . '" ' . $a11y, $cache[ $name ], 1 );

		if ( $args['echo'] ) {
			echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput -- static, repo-controlled SVG asset.
			return;
		}

		return $svg;
	}
}

if ( ! function_exists( 'dmf_thumbnail' ) ) {
	/**
	 * Responsive lazy thumbnail with an aspect-preserving placeholder.
	 *
	 * @param int                   $post_id Post ID.
	 * @param string                $size    Image size.
	 * @param array<string, string> $attr    Extra img attributes.
	 */
	function dmf_thumbnail( int $post_id, string $size = 'dmf-card', array $attr = array() ): void {
		if ( ! has_post_thumbnail( $post_id ) ) {
			echo '<span class="dmf-media__placeholder" aria-hidden="true">' . dmf_icon( 'image', array( 'echo' => false ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
			return;
		}

		$attr = wp_parse_args(
			$attr,
			array(
				'loading'  => 'lazy',
				'decoding' => 'async',
				'class'    => 'dmf-media__img',
			)
		);

		echo get_the_post_thumbnail( $post_id, $size, $attr ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

if ( ! function_exists( 'dmf_field' ) ) {
	/**
	 * A listing custom field value.
	 *
	 * @param string           $key      Field key (e.g. "capacity").
	 * @param int|WP_Post|null $post     Post.
	 * @param mixed            $fallback Fallback value.
	 */
	function dmf_field( string $key, int|WP_Post|null $post = null, mixed $fallback = '' ): mixed {
		$post = get_post( $post );
		return $post ? FieldRegistry::value( $post->ID, $key, $fallback ) : $fallback;
	}
}

if ( ! function_exists( 'dmf_listing_meta' ) ) {
	/**
	 * Back-compat alias of dmf_field() used by card templates.
	 *
	 * @param string           $key  Field key.
	 * @param int|WP_Post|null $post Post.
	 */
	function dmf_listing_meta( string $key, int|WP_Post|null $post = null ): mixed {
		return dmf_field( $key, $post );
	}
}

if ( ! function_exists( 'dmf_listing_post_types' ) ) {
	/**
	 * Post types that behave as listings.
	 *
	 * @return string[]
	 */
	function dmf_listing_post_types(): array {
		/**
		 * Filter the post types treated as listings.
		 *
		 * @param string[] $types Post type slugs.
		 */
		return (array) apply_filters( 'dmf/listing_post_types', array( PostTypes::LISTING ) );
	}
}

if ( ! function_exists( 'dmf_is_listing' ) ) {
	/**
	 * Whether a post is a listing.
	 *
	 * @param int|WP_Post|null $post Post.
	 */
	function dmf_is_listing( int|WP_Post|null $post = null ): bool {
		return in_array( get_post_type( $post ), dmf_listing_post_types(), true );
	}
}

if ( ! function_exists( 'dmf_listing_type' ) ) {
	/**
	 * The ListingType config object for a listing.
	 *
	 * @param int|WP_Post|null $post Post.
	 */
	function dmf_listing_type( int|WP_Post|null $post = null ): ?\DMF\Modules\ListingTypes\ListingType {
		$post = get_post( $post );
		if ( ! $post ) {
			return null;
		}
		$slug = PostTypes::listing_type_slug( $post->ID );
		return $slug ? dmf()->get( TypeRegistry::class )->get( $slug ) : null;
	}
}

if ( ! function_exists( 'dmf_listing_kind' ) ) {
	/**
	 * Card variant for a listing (venue|hotel|experience|event|listing),
	 * driven by the listing type's configured card.
	 *
	 * @param int|WP_Post|null $post Post.
	 */
	function dmf_listing_kind( int|WP_Post|null $post = null ): string {
		$type = dmf_listing_type( $post );

		/**
		 * Filter the resolved card variant for a listing.
		 *
		 * @param string       $kind Card variant.
		 * @param WP_Post|null $post Post object.
		 */
		return (string) apply_filters( 'dmf/listing_kind', $type->card ?? 'listing', get_post( $post ) );
	}
}

if ( ! function_exists( 'dmf_listing_primary_term' ) ) {
	/**
	 * The badge term for a listing card: its listing type term.
	 *
	 * @param int|WP_Post|null $post Post.
	 */
	function dmf_listing_primary_term( int|WP_Post|null $post = null ): ?WP_Term {
		$post = get_post( $post );
		if ( ! $post ) {
			return null;
		}

		foreach ( array( PostTypes::TYPE_TAXONOMY, PostTypes::CATEGORY ) as $taxonomy ) {
			$terms = get_the_terms( $post, $taxonomy );
			if ( is_array( $terms ) && $terms ) {
				return $terms[0];
			}
		}

		return null;
	}
}

if ( ! function_exists( 'dmf_listing_rating' ) ) {
	/**
	 * Average review rating for a listing.
	 *
	 * @param int|WP_Post|null $post Post.
	 */
	function dmf_listing_rating( int|WP_Post|null $post = null ): float {
		$post = get_post( $post );
		if ( ! $post ) {
			return 0.0;
		}

		$value = get_post_meta( $post->ID, '_dmf_rating', true );

		return is_numeric( $value ) ? round( (float) $value, 1 ) : 0.0;
	}
}

if ( ! function_exists( 'dmf_listing_query' ) ) {
	/**
	 * Query listings through the repository (blocks, sections, templates).
	 *
	 * @param array<string, mixed> $args Repository args (see Repository::query()).
	 */
	function dmf_listing_query( array $args = array() ): WP_Query {
		// Back-compat with the ported section templates ('count' arg).
		if ( isset( $args['count'] ) && ! isset( $args['per_page'] ) ) {
			$args['per_page'] = (int) $args['count'];
		}

		return dmf()->get( Repository::class )->query( $args );
	}
}

if ( ! function_exists( 'dmf_favorite_button' ) ) {
	/**
	 * Render the save-to-shortlist toggle for a listing.
	 *
	 * @param int|WP_Post|null $post Post.
	 */
	function dmf_favorite_button( int|WP_Post|null $post = null ): void {
		$post = get_post( $post );
		if ( ! $post || ! dmf()->has_module( 'favorites' ) ) {
			return;
		}

		$active = is_user_logged_in()
			&& dmf()->get( FavoritesModule::class )->has( get_current_user_id(), $post->ID );

		printf(
			'<button type="button" class="dmf-favorite%s" data-dmf-favorite="%d" aria-pressed="%s" aria-label="%s">%s</button>',
			$active ? ' is-active' : '',
			(int) $post->ID,
			$active ? 'true' : 'false',
			esc_attr__( 'Save to my shortlist', 'dmf' ),
			dmf_icon( 'star', array( 'echo' => false ) ) // phpcs:ignore WordPress.Security.EscapeOutput
		);
	}
}

if ( ! function_exists( 'dmf_breadcrumbs' ) ) {
	/**
	 * Semantic breadcrumb trail with BreadcrumbList JSON-LD (in Seo module).
	 */
	function dmf_breadcrumbs(): void {
		$crumbs = array(
			array(
				'label' => __( 'Home', 'dmf' ),
				'url'   => home_url( '/' ),
			),
		);

		if ( dmf_is_listing() && is_singular() ) {
			$term = dmf_listing_primary_term();
			if ( $term ) {
				$crumbs[] = array(
					'label' => $term->name,
					'url'   => (string) get_term_link( $term ),
				);
			}
			$crumbs[] = array( 'label' => get_the_title(), 'url' => '' );
		} elseif ( is_tax() || is_category() || is_tag() ) {
			$crumbs[] = array( 'label' => single_term_title( '', false ), 'url' => '' );
		} elseif ( is_post_type_archive() ) {
			$crumbs[] = array( 'label' => post_type_archive_title( '', false ), 'url' => '' );
		} elseif ( is_singular() ) {
			$crumbs[] = array( 'label' => get_the_title(), 'url' => '' );
		} elseif ( is_search() ) {
			$crumbs[] = array( 'label' => __( 'Search results', 'dmf' ), 'url' => '' );
		}

		/**
		 * Filter the breadcrumb trail.
		 *
		 * @param array<int, array{label: string, url: string}> $crumbs Trail.
		 */
		$crumbs = (array) apply_filters( 'dmf/breadcrumbs', $crumbs );

		if ( count( $crumbs ) < 2 ) {
			return;
		}

		echo '<nav class="dmf-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'dmf' ) . '"><ol>';
		$last = array_key_last( $crumbs );
		foreach ( $crumbs as $i => $crumb ) {
			echo '<li>';
			if ( $i !== $last && ! empty( $crumb['url'] ) ) {
				printf( '<a href="%s">%s</a>', esc_url( (string) $crumb['url'] ), esc_html( (string) $crumb['label'] ) );
			} else {
				printf( '<span aria-current="page">%s</span>', esc_html( (string) $crumb['label'] ) );
			}
			echo '</li>';
		}
		echo '</ol></nav>';
	}
}
