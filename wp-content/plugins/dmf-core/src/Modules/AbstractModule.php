<?php
/**
 * Base module.
 *
 * @package DMF
 */

namespace DMF\Modules;

use DMF\Contracts\Module;
use DMF\Support\Container;

defined( 'ABSPATH' ) || exit;

/**
 * Convenience base class — modules override only what they need.
 */
abstract class AbstractModule implements Module {

	public function __construct( protected Container $container ) {}

	public function register(): void {}

	public function booted(): void {}

	public function install(): void {}
}
