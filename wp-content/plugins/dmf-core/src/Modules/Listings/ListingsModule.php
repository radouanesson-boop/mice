<?php
/**
 * Listings module — the heart of the framework.
 *
 * @package DMF
 */

namespace DMF\Modules\Listings;

use DMF\Modules\AbstractModule;
use DMF\Modules\Listings\Fields\Field;
use DMF\Modules\Listings\Fields\MetaBox;
use DMF\Support\Template;

defined( 'ABSPATH' ) || exit;

/**
 * Registers every configured listing type as a post type, the shared
 * taxonomies, the field system, meta registration and the front-end
 * template routing (archive/single via the theme-overridable template
 * engine).
 */
final class ListingsModule extends AbstractModule {

	private TypeRegistry $registry;

	public function registry(): TypeRegistry {
		return $this->registry ??= new TypeRegistry();
	}

	public function register(): void {
		$this->container->singleton( TypeRegistry::class, fn() => $this->registry() );

		add_action( 'init', array( $this, 'register_taxonomies' ), 8 );
		add_action( 'init', array( $this, 'register_post_types' ), 9 );
		add_action( 'init', array( $this, 'register_meta' ), 10 );

		( new MetaBox() )->hooks();

		add_filter( 'template_include', array( $this, 'route_templates' ), 20 );
	}

	public function install(): void {
		// Ensure types are registered before the kernel flushes rewrites.
		$this->register_taxonomies();
		$this->register_post_types();
	}

	/**
	 * Shared taxonomies — every listing type opts into the ones it needs.
	 * Extra taxonomies can be added with the `dmf/taxonomies` filter.
	 */
	public function register_taxonomies(): void {
		$taxonomies = apply_filters(
			'dmf/taxonomies',
			array(
				'dmf_category' => array(
					'label'        => __( 'Listing Categories', 'dmf' ),
					'hierarchical' => true,
					'rewrite'      => 'listing-category',
				),
				'dmf_area'     => array(
					'label'        => __( 'Areas & Districts', 'dmf' ),
					'hierarchical' => true,
					'rewrite'      => 'area',
				),
				'dmf_amenity'  => array(
					'label'        => __( 'Amenities', 'dmf' ),
					'hierarchical' => false,
					'rewrite'      => 'amenity',
				),
				'dmf_tag'      => array(
					'label'        => __( 'Listing Tags', 'dmf' ),
					'hierarchical' => false,
					'rewrite'      => 'listing-tag',
				),
			)
		);

		foreach ( $taxonomies as $key => $config ) {
			register_taxonomy(
				$key,
				array(), // Object types attach in register_post_types().
				array(
					'label'             => $config['label'],
					'public'            => true,
					'hierarchical'      => (bool) $config['hierarchical'],
					'show_admin_column' => true,
					'show_in_rest'      => true,
					'rewrite'           => array( 'slug' => $config['rewrite'] ),
				)
			);
		}
	}

	/**
	 * One CPT per configured listing type.
	 */
	public function register_post_types(): void {
		foreach ( $this->registry()->all() as $type ) {
			register_post_type(
				$type->slug,
				array(
					'labels'       => array(
						'name'          => $type->plural,
						'singular_name' => $type->singular,
						/* translators: %s: listing type singular label. */
						'add_new_item'  => sprintf( __( 'Add %s', 'dmf' ), $type->singular ),
						/* translators: %s: listing type singular label. */
						'edit_item'     => sprintf( __( 'Edit %s', 'dmf' ), $type->singular ),
					),
					'public'       => true,
					'has_archive'  => $type->has_archive ? $type->rewrite : false,
					'rewrite'      => array(
						'slug'       => $type->rewrite,
						'with_front' => false,
					),
					'menu_icon'    => $type->icon,
					'show_in_rest' => true,
					'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
					'taxonomies'   => $type->taxonomies,
					'capability_type' => 'dmf_listing',
					'map_meta_cap' => true,
				)
			);

			foreach ( $type->taxonomies as $taxonomy ) {
				register_taxonomy_for_object_type( $taxonomy, $type->slug );
			}
		}
	}

	/**
	 * Register every configured field as post meta so values are exposed
	 * to the REST API and the block editor with proper sanitization.
	 */
	public function register_meta(): void {
		foreach ( $this->registry()->all() as $type ) {
			foreach ( $type->fields as $group ) {
				foreach ( (array) ( $group['fields'] ?? array() ) as $config ) {
					$field = Field::from_array( (array) $config );

					if ( '' === $field->key ) {
						continue;
					}

					$is_array = in_array( $field->type, array( 'multiselect', 'gallery', 'geo', 'hours', 'social' ), true );

					register_post_meta(
						$type->slug,
						$field->meta_key(),
						array(
							'single'            => true,
							'type'              => $is_array ? 'object' : 'string',
							'show_in_rest'      => $is_array
								? array( 'schema' => array( 'type' => array( 'object', 'array' ), 'additionalProperties' => true ) )
								: true,
							'sanitize_callback' => fn( $value ) => $field->sanitize( $value ),
							'auth_callback'     => fn( $allowed, $meta_key, $post_id ) => current_user_can( 'edit_post', $post_id ),
						)
					);
				}
			}
		}
	}

	/**
	 * Route listing archives & singles through DMF templates (which the
	 * theme can override in {theme}/dmf/). Falls back to the theme's own
	 * archive-{cpt}.php / single-{cpt}.php when it ships one.
	 */
	public function route_templates( string $template ): string {
		$post_types = $this->registry()->post_types();

		if ( is_singular( $post_types ) && ! $this->theme_has( 'single-' . get_post_type() ) ) {
			return Template::locate( 'single-listing' );
		}

		if ( is_post_type_archive( $post_types ) && ! $this->theme_has( 'archive-' . get_query_var( 'post_type' ) ) ) {
			return Template::locate( 'archive-listing' );
		}

		return $template;
	}

	private function theme_has( string $name ): bool {
		return '' !== locate_template( array( $name . '.php' ) );
	}
}
