<?php
/**
 * Framework kernel.
 *
 * @package DMF
 */

namespace DMF;

use DMF\Contracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

/**
 * The kernel wires the container, resolves the active module set and runs the
 * two-phase module lifecycle (register services → boot hooks).
 */
final class Plugin {

	/**
	 * Singleton instance.
	 */
	private static ?Plugin $instance = null;

	/**
	 * Service container.
	 */
	private Container $container;

	/**
	 * Active modules keyed by name.
	 *
	 * @var array<string, ModuleInterface>
	 */
	private array $modules = array();

	/**
	 * Whether boot() already ran.
	 */
	private bool $booted = false;

	/**
	 * Core module classes shipped with the framework, in boot order.
	 *
	 * @var array<int, class-string<ModuleInterface>>
	 */
	private const CORE_MODULES = array(
		Modules\I18n\I18nModule::class,
		Modules\Settings\SettingsModule::class,
		Modules\Installer\InstallerModule::class,
		Modules\ListingTypes\ListingTypesModule::class,
		Modules\Fields\FieldsModule::class,
		Modules\Listings\ListingsModule::class,
		Modules\Search\SearchModule::class,
		Modules\Maps\MapsModule::class,
		Modules\Events\EventsModule::class,
		Modules\Members\MembersModule::class,
		Modules\Favorites\FavoritesModule::class,
		Modules\Reviews\ReviewsModule::class,
		Modules\Inquiries\InquiriesModule::class,
		Modules\Notifications\NotificationsModule::class,
		Modules\Seo\SeoModule::class,
		Modules\Templates\TemplatesModule::class,
		Modules\Blocks\BlocksModule::class,
		Modules\Shortcodes\ShortcodesModule::class,
		Modules\Api\ApiModule::class,
		Modules\ImportExport\ImportExportModule::class,
		Modules\Performance\PerformanceModule::class,
		Modules\Admin\AdminModule::class,
		Modules\Cli\CliModule::class,
	);

	/**
	 * Modules that must never be disabled from the UI.
	 *
	 * @var string[]
	 */
	public const LOCKED_MODULES = array( 'i18n', 'settings', 'installer', 'listing-types', 'fields', 'listings', 'templates' );

	/**
	 * Private constructor — use instance().
	 */
	private function __construct() {
		$this->container = new Container();
		$this->container->instance( Container::class, $this->container );
		$this->container->instance( self::class, $this );
	}

	/**
	 * Kernel accessor.
	 */
	public static function instance(): Plugin {
		return self::$instance ??= new self();
	}

	/**
	 * Service container accessor.
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Shorthand service resolver: dmf()->get( SearchQuery::class ).
	 *
	 * @param string $id Service id.
	 */
	public function get( string $id ): mixed {
		return $this->container->get( $id );
	}

	/**
	 * Active modules keyed by name.
	 *
	 * @return array<string, ModuleInterface>
	 */
	public function modules(): array {
		return $this->modules;
	}

	/**
	 * Whether a module is active.
	 *
	 * @param string $name Module name, e.g. "events".
	 */
	public function has_module( string $name ): bool {
		return isset( $this->modules[ $name ] );
	}

	/**
	 * Resolve module set, register services, boot hooks.
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		/**
		 * Filter the module class list. Extension plugins append their own
		 * module classes here; they get the same lifecycle as core modules.
		 *
		 * @param array<int, class-string<ModuleInterface>> $classes Module classes.
		 */
		$classes = (array) apply_filters( 'dmf/modules', self::CORE_MODULES );

		$disabled = (array) get_option( 'dmf_disabled_modules', array() );

		foreach ( $classes as $class ) {
			if ( ! is_string( $class ) || ! class_exists( $class ) || ! is_subclass_of( $class, ModuleInterface::class ) ) {
				continue;
			}

			/** @var ModuleInterface $module */
			$module = $this->container->build( $class );
			$name   = $module->name();

			if ( in_array( $name, $disabled, true ) && ! in_array( $name, self::LOCKED_MODULES, true ) ) {
				continue;
			}

			$this->modules[ $name ] = $module;
		}

		/**
		 * Fires before services are registered. Bind or override services here.
		 *
		 * @param Container $container Framework container.
		 */
		do_action( 'dmf/container', $this->container );

		foreach ( $this->modules as $module ) {
			$module->register( $this->container );
		}

		foreach ( $this->modules as $module ) {
			$module->boot( $this->container );
		}

		/**
		 * Fires once the framework has fully booted.
		 *
		 * @param Plugin $plugin Kernel instance.
		 */
		do_action( 'dmf/booted', $this );
	}
}
