<?php
/**
 * Module contract.
 *
 * @package DMF
 */

namespace DMF\Contracts;

use DMF\Container;

defined( 'ABSPATH' ) || exit;

/**
 * Every framework feature is a module: a self-contained unit that binds its
 * services into the container and attaches its WordPress hooks. Modules can
 * be disabled per-site from Settings → Modules or via the `dmf/modules`
 * filter, so a Tourism Board that needs no event engine simply turns it off.
 */
interface ModuleInterface {

	/**
	 * Unique machine name, e.g. "listings".
	 */
	public function name(): string;

	/**
	 * Bind services into the container. Runs for every active module before
	 * any module boots, so cross-module services are always available.
	 *
	 * @param Container $container Framework container.
	 */
	public function register( Container $container ): void;

	/**
	 * Attach WordPress hooks. Runs on `plugins_loaded` (priority 5).
	 *
	 * @param Container $container Framework container.
	 */
	public function boot( Container $container ): void;
}
