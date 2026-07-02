<?php
/**
 * Module contract.
 *
 * @package DMF
 */

namespace DMF\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * Every DMF feature area is a module implementing this interface.
 */
interface Module {

	/**
	 * Bind hooks and container definitions. Runs on plugins_loaded.
	 */
	public function register(): void;

	/**
	 * Cross-module wiring. Runs after ALL modules have registered.
	 */
	public function booted(): void;

	/**
	 * One-time install work (capabilities, options, tables). Runs on
	 * plugin activation. Must be idempotent.
	 */
	public function install(): void;
}
