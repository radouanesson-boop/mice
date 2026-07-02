<?php
/**
 * Settings module.
 *
 * @package DMF
 */

namespace DMF\Modules\Settings;

use DMF\Container;
use DMF\Support\AbstractModule;
use DMF\Support\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Binds the Options service and registers the setting with WordPress so it
 * is exposed (permission-gated) through the core /wp/v2/settings surface and
 * survives export tooling. The admin UI lives in the Admin module; the REST
 * write surface in the Api module.
 */
class SettingsModule extends AbstractModule {

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return 'settings';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register( Container $container ): void {
		$container->singleton( Options::class );
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {
		add_action(
			'init',
			static function () {
				register_setting(
					'dmf',
					Options::OPTION,
					array(
						'type'              => 'object',
						'default'           => array(),
						'show_in_rest'      => false, // Exposed via dmf/v1/settings with capability checks instead.
						'sanitize_callback' => array( Sanitizer::class, 'sanitize' ),
					)
				);
			}
		);
	}
}
