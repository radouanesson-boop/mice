<?php
/**
 * Plugin kernel: service container + module lifecycle.
 *
 * @package DMF
 */

namespace DMF;

use DMF\Support\Container;
use DMF\Contracts\Module;

defined( 'ABSPATH' ) || exit;

/**
 * The framework kernel.
 *
 * Modules are the unit of everything in DMF. Each module is a class
 * implementing {@see Module}; the active list is filterable so a site (or a
 * future DMF add-on plugin) can add, replace or remove modules without
 * touching core:
 *
 *     add_filter( 'dmf/modules', fn( array $m ) => $m + [ 'crm' => MyCrmModule::class ] );
 */
final class Plugin {

	private static ?Plugin $instance = null;

	private Container $container;

	/**
	 * Booted module instances, keyed by slug.
	 *
	 * @var array<string, Module>
	 */
	private array $modules = array();

	private function __construct() {
		$this->container = new Container();
	}

	public static function instance(): Plugin {
		return self::$instance ??= new self();
	}

	public function container(): Container {
		return $this->container;
	}

	/**
	 * Get a booted module by slug (e.g. dmf()->module( 'listings' )).
	 */
	public function module( string $slug ): ?Module {
		return $this->modules[ $slug ] ?? null;
	}

	/**
	 * Default module map. Order matters only for registration priority;
	 * runtime coupling goes through hooks, never direct calls.
	 *
	 * @return array<string, class-string<Module>>
	 */
	public function default_modules(): array {
		return array(
			'core'        => Modules\Core\CoreModule::class,
			'listings'    => Modules\Listings\ListingsModule::class,
			'search'      => Modules\Search\SearchModule::class,
			'maps'        => Modules\Maps\MapsModule::class,
			'events'      => Modules\Events\EventsModule::class,
			'members'     => Modules\Members\MembersModule::class,
			'favorites'   => Modules\Favorites\FavoritesModule::class,
			'reviews'     => Modules\Reviews\ReviewsModule::class,
			'forms'       => Modules\Forms\FormsModule::class,
			'seo'         => Modules\Seo\SeoModule::class,
			'api'         => Modules\Api\ApiModule::class,
			'blocks'      => Modules\Blocks\BlocksModule::class,
			'shortcodes'  => Modules\Shortcodes\ShortcodesModule::class,
			'admin'       => Modules\Admin\AdminModule::class,
			'i18n'        => Modules\I18n\I18nModule::class,
			'performance' => Modules\Performance\PerformanceModule::class,
			'cli'         => Modules\Cli\CliModule::class,
		);
	}

	/**
	 * Boot the framework: resolve the module list, register, then boot.
	 */
	public function boot(): void {
		/**
		 * Filter the active module map.
		 *
		 * @param array<string, class-string<Module>> $modules slug => class.
		 */
		$map = apply_filters( 'dmf/modules', $this->default_modules() );

		// Two-phase lifecycle: register() binds hooks/definitions; boot()
		// runs after every module has registered, for cross-module wiring.
		foreach ( $map as $slug => $class ) {
			if ( ! class_exists( $class ) || ! is_subclass_of( $class, Module::class ) ) {
				continue;
			}

			$module = new $class( $this->container );
			$module->register();
			$this->modules[ $slug ] = $module;
		}

		foreach ( $this->modules as $module ) {
			$module->booted();
		}

		/**
		 * Fires when the framework has fully booted.
		 *
		 * @param Plugin $plugin The kernel.
		 */
		do_action( 'dmf/booted', $this );
	}

	/**
	 * Activation: let every module install itself, then flush rewrites.
	 */
	public static function activate(): void {
		$plugin = self::instance();

		if ( empty( $plugin->modules ) ) {
			$plugin->boot();
		}

		foreach ( $plugin->modules as $module ) {
			$module->install();
		}

		update_option( 'dmf_version', DMF_VERSION );
		flush_rewrite_rules();
	}

	/**
	 * Deactivation: non-destructive. Data removal happens in uninstall.php
	 * only when the site owner opts in.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
