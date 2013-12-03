<?php

namespace ServiceRegistry;

use Closure;

use InvalidArgumentException;
use RuntimeException;

/**
 * Provides a minimalistic service registry
 *
 * @group ServiceRegistry
 *
 * @licence GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class ServiceRegistry implements RegistryInterface {

	/** @var ServiceRegistry */
	protected static $instance = null;

	/** @var array */
	protected $services = array();

	/**
	 * @since 0.1
	 *
	 * @param array $services
	 */
	public function __construct( array $services = array() ) {
		$this->services = $services;
	}

	/**
	 * @since 0.1
	 *
	 * @return ServiceRegistry
	 */
	public static function getInstance() {

		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Reset instance
	 *
	 * @since 0.1
	 */
	public static function reset() {
		self::$instance = null;
	}

	/**
	 * @since 0.1
	 *
	 * @param ServiceContainer|null $container
	 *
	 * @throws RuntimeException
	 */
	public function registerContainer( ServiceContainer $container = null ) {

		$definitions = $container->loadAllDefinitions();

		if ( !( $definitions instanceof Closure ) ) {
			throw new RuntimeException( 'Container ought to return a closure' );
		}

		$definitions( $this );
	}

	/**
	 * @since 0.1
	 *
	 * @param string $objectName
	 * @param Closure $objectSignature
	 * @param string|null $objectScope
	 *
	 * @throws InvalidArgumentException
	 */
	public function registerObject( $objectName, $objectSignature, $objectScope = null ) {

		if ( !is_string( $objectName ) ) {
			throw new InvalidArgumentException( 'The key is expected to be a string' );
		}

		if ( !( $objectSignature instanceof Closure ) ) {
			throw new InvalidArgumentException( 'The object signature ought to be a closure' );
		}

		$this->attach( $objectName, $objectScope !== null ? $this->addSingleton( $objectSignature ) : $objectSignature );
	}

	/**
	 * @since 0.1
	 *
	 * @param string $objectName
	 * @param array|null $arguments
	 *
	 * @return mixed
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function newObject( $objectName, $arguments = null ) {

		if ( !is_string( $objectName ) ) {
			throw new InvalidArgumentException( 'The object name is expected to be a string' );
		}

		if ( !$this->contains( $objectName ) ) {
			throw new RuntimeException( "Requested {$objectName} service is not available" );
		}

		$this->addArguments( $arguments );

		$objectSignature = $this->lookup( $objectName );

		return is_callable( $objectSignature ) ? $objectSignature( $this ) : $objectSignature;
	}

	/**
	 * @since 0.1
	 *
	 * @return array
	 */
	public function getAllServices() {
		return $this->services;
	}

	/**
	 * @since 0.1
	 */
	protected function addSingleton( $value ) {
		return function ( $builder ) use ( $value ) {
			static $singleton;

			if ( $singleton === null ) {
				$singleton = $value( $builder );
			}

			return $singleton;
		};
	}

	/**
	 * @since 0.1
	 *
	 * @throws InvalidArgumentException
	 */
	protected function addArguments( $arguments ) {

		if ( is_array( $arguments ) ) {

			foreach ( $arguments as $key => $value ) {

				if ( !is_string( $key ) ) {
					throw new InvalidArgumentException( 'The argument key is expected to be a string' );
				}

				$this->attach( $key, function() use( $value ) { return $value; } );
			}
		}

	}

	/**
	 * @since 0.1
	 */
	protected function contains( $key ) {
		return isset( $this->services[$key] ) || array_key_exists( $key, $this->services );
	}

	/**
	 * @since 0.1
	 */
	protected function attach( $key, $value = null ) {
		$this->services[$key] = $value;
	}

	/**
	 * @since 0.1
	 */
	protected function lookup( $key ) {
		return $this->services[$key];
	}

}
