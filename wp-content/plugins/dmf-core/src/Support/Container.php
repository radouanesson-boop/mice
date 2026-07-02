<?php
/**
 * Service container.
 *
 * @package DMF
 */

namespace DMF\Support;

defined( 'ABSPATH' ) || exit;

/**
 * A deliberately small DI container: bindings, singletons, factories.
 * No reflection magic — everything is explicit and greppable.
 */
final class Container {

	/** @var array<string, callable> */
	private array $factories = array();

	/** @var array<string, mixed> */
	private array $instances = array();

	/**
	 * Bind a factory. Each get() call invokes it.
	 */
	public function bind( string $id, callable $factory ): void {
		$this->factories[ $id ] = $factory;
		unset( $this->instances[ $id ] );
	}

	/**
	 * Bind a shared (singleton) service.
	 */
	public function singleton( string $id, callable $factory ): void {
		$this->factories[ $id ] = function ( Container $c ) use ( $id, $factory ) {
			return $this->instances[ $id ] ??= $factory( $c );
		};
	}

	/**
	 * Bind an existing instance.
	 */
	public function instance( string $id, mixed $value ): void {
		$this->instances[ $id ] = $value;
		unset( $this->factories[ $id ] );
	}

	public function has( string $id ): bool {
		return isset( $this->instances[ $id ] ) || isset( $this->factories[ $id ] );
	}

	/**
	 * Resolve a service.
	 *
	 * @throws \RuntimeException When the id is unknown.
	 */
	public function get( string $id ): mixed {
		if ( array_key_exists( $id, $this->instances ) && ! isset( $this->factories[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		if ( isset( $this->factories[ $id ] ) ) {
			return ( $this->factories[ $id ] )( $this );
		}

		throw new \RuntimeException( esc_html( "DMF container: unknown service '{$id}'." ) );
	}
}
