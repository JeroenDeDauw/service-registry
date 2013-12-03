<?php

namespace ServiceRegistry\Test;

use ServiceRegistry\ServiceRegistry;

use RuntimeException;

/**
 * @group ServiceRegistry
 *
 * @licence GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class BenchmarkTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @since 0.1
	 */
	public function newObjectGraph() {

		return function( $registry ) {

			$registry->registerObject( 'Foo', function ( $registry ) {
				return new \stdClass;
			} );

			$registry->registerObject( 'Fam', function ( $registry ) {
				return $registry->newObject( 'Foo' );
			} );

			$registry->registerObject( 'Bar', function ( $registry ) {

				$fam = $registry->newObject( 'Fam' );

				$mock = new ExtensibleMock();
				$mock->fam1 = function() use ( $fam ) { return $fam; };
				$mock->fam2 = function() use ( $fam ) { return $fam; };

				return $mock;
			} );

			$registry->registerObject( 'Foz', function ( $registry ) {
				$bar = $registry->newObject( 'Bar' );

				if ( $bar->fam1() !== $bar->fam2() ) {
					throw new RuntimeException( 'Objects should be equal' );
				}

				return $bar;
			} );

		};

	}

	/**
	 * @since 0.1
	 */
	public function testNewObjectRuntimeComparison() {

		$container = $this->getMockForAbstractClass( '\ServiceRegistry\ServiceContainer' );

		$container->expects( $this->any() )
			->method( 'loadAllDefinitions' )
			->will( $this->returnValue( $this->newObjectGraph() ) );

		$instance = new ServiceRegistry( $container );

		echo "\n";

		$counter = 1000;
		$s = array();
		$time = microtime( true );

		for( $x = 0; $x < $counter; $x++ ) {
			$s[] = $instance->newObject( 'Foz' );
		};

		echo 'memory: ' . memory_get_peak_usage() . ' time: ' . ( ( microtime( true ) - $time ) / $counter ) . " sec\n";
		unset( $s );

		echo "\n";
		ServiceRegistry::getInstance()->registerContainer( $container );

		$counter = 1000;
		$s = array();
		$time = microtime( true );

		for( $x = 0; $x < $counter; $x++ ) {
			$s[] = ServiceRegistry::getInstance()->newObject( 'Foz' );
		};

		echo 'memory: ' . memory_get_peak_usage() . ' time: ' . ( ( microtime( true ) - $time ) / $counter ) . " sec\n";
		unset( $s );

		$this->assertTrue( true );
	}

}

class ExtensibleMock {

	public function __call( $method, $args ) {
		if ( isset( $this->$method ) && is_callable( $this->$method ) ) {
			$func = $this->$method;
			return call_user_func_array( $func, $args );
		}
	}
}