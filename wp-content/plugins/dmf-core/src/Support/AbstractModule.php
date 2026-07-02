<?php
/**
 * Base module.
 *
 * @package DMF
 */

namespace DMF\Support;

use DMF\Container;
use DMF\Contracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Convenience base class: modules only override what they need.
 */
abstract class AbstractModule implements ModuleInterface {

	/**
	 * {@inheritDoc}
	 */
	public function register( Container $container ): void {}

	/**
	 * {@inheritDoc}
	 */
	public function boot( Container $container ): void {}
}
