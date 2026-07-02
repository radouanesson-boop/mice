<?php
/**
 * Fields module.
 *
 * @package DMF
 */

namespace DMF\Modules\Fields;

use DMF\Container;
use DMF\Support\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * The custom field engine: reusable field groups, typed sanitization,
 * admin meta boxes and media-picker assets.
 */
class FieldsModule extends AbstractModule {

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'fields';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register( Container $container ): void {
		$container->singleton( FieldRegistry::class );
		$container->singleton( FieldTypes::class );
		$container->singleton( MetaBox::class );
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		if ( is_admin() ) {
			$container->get( MetaBox::class )->hooks();
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		}
	}

	/**
	 * Media-picker helper for attachment/gallery fields.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function admin_assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'dmf-fields',
			DMF_URL . 'assets/admin/fields.js',
			array(),
			DMF_VERSION,
			array( 'in_footer' => true )
		);
	}
}
