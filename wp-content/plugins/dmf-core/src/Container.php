<?php
/**
 * Lightweight dependency-injection container (PSR-11 compatible surface).
 *
 * @package DMF
 */

namespace DMF;

defined( 'ABSPATH' ) || exit;

/**
 * Service container with lazy singletons, factories and reflection-based
 * constructor autowiring for framework classes.
 */
class Container {

	/**
	 * Factory closures keyed by abstract id.
	 *
	 * @var array<string, callable>
	 */
	private array $bindings = array();

	/**
	 * Ids that should resolve once and be cached.
	 *
	 * @var array<string, bool>
	 */
	private array $shared = array();

	/**
	 * Resolved singleton instances.
	 *
	 * @var array<string, mixed>
	 */
	private array $instances = array();

	/**
	 * Bind a factory.
	 *
	 * @param string        $id      Abstract id (usually a class/interface name).
	 * @param callable|null $factory Factory receiving the container. Null = autowire $id.
	 */
	public function bind( string $id, ?callable $factory = null ): void {
		$this->bindings[ $id ] = $factory ?? fn( Container $c ) => $c->build( $id );
		unset( $this->shared[ $id ], $this->instances[ $id ] );
	}

	/**
	 * Bind a shared (singleton) factory.
	 *
	 * @param string        $id      Abstract id.
	 * @param callable|null $factory Factory receiving the container. Null = autowire $id.
	 */
	public function singleton( string $id, ?callable $factory = null ): void {
		$this->bind( $id, $factory );
		$this->shared[ $id ] = true;
	}

	/**
	 * Register an existing instance.
	 *
	 * @param string $id       Abstract id.
	 * @param mixed  $instance Concrete instance.
	 */
	public function instance( string $id, mixed $instance ): void {
		$this->instances[ $id ] = $instance;
		$this->shared[ $id ]    = true;
	}

	/**
	 * Whether the container can resolve an id.
	 *
	 * @param string $id Abstract id.
	 */
	public function has( string $id ): bool {
		return isset( $this->instances[ $id ] ) || isset( $this->bindings[ $id ] ) || class_exists( $id );
	}

	/**
	 * Resolve an id.
	 *
	 * @param string $id Abstract id.
	 * @return mixed
	 *
	 * @throws \RuntimeException When the id cannot be resolved.
	 */
	public function get( string $id ): mixed {
		if ( isset( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		if ( isset( $this->bindings[ $id ] ) ) {
			$object = ( $this->bindings[ $id ] )( $this );
		} elseif ( class_exists( $id ) ) {
			$object = $this->build( $id );
		} else {
			throw new \RuntimeException( esc_html( "DMF container cannot resolve '{$id}'." ) );
		}

		if ( ! empty( $this->shared[ $id ] ) || ! isset( $this->bindings[ $id ] ) ) {
			// Autowired classes default to singletons — framework services are stateless.
			$this->instances[ $id ] = $object;
		}

		return $object;
	}

	/**
	 * Instantiate a class, resolving type-hinted constructor dependencies.
	 *
	 * @param string $class Concrete class name.
	 * @return object
	 *
	 * @throws \RuntimeException When a dependency cannot be autowired.
	 */
	public function build( string $class ): object {
		$reflection  = new \ReflectionClass( $class );
		$constructor = $reflection->getConstructor();

		if ( ! $constructor || 0 === $constructor->getNumberOfParameters() ) {
			return new $class();
		}

		$arguments = array();

		foreach ( $constructor->getParameters() as $parameter ) {
			$type = $parameter->getType();

			if ( $type instanceof \ReflectionNamedType && ! $type->isBuiltin() ) {
				$arguments[] = Container::class === $type->getName() ? $this : $this->get( $type->getName() );
			} elseif ( $parameter->isDefaultValueAvailable() ) {
				$arguments[] = $parameter->getDefaultValue();
			} else {
				throw new \RuntimeException(
					esc_html( "DMF container cannot autowire parameter \${$parameter->getName()} of {$class}." )
				);
			}
		}

		return $reflection->newInstanceArgs( $arguments );
	}
}
