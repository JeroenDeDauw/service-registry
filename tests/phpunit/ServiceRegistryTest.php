<?php

namespace ServiceRegistry\Test;

use ServiceRegistry\ServiceRegistry;
use ServiceRegistry\ServiceContainer;

/**
 * @covers \ServiceRegistry\ServiceRegistry
 *
 * @group ServiceRegistry
 *
 * @licence GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class ServiceRegistryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @since 0.1
	 */
	public function newServiceContainer( $return = null ) {

		$container = $this->getMockForAbstractClass( '\ServiceRegistry\ServiceContainer' );

		$container->expects( $this->any() )
			->method( 'loadAllDefinitions' )
			->will( $this->returnValue( $return ) );

		return $container;
	}

	/**
	 * @since 0.1
	 */
	public function newInstance( ServiceContainer $container = null ) {
		return new ServiceRegistry( $container );
	}

	/**
	 * @since 0.1
	 */
	public function testCanConstruct() {
		$this->assertInstanceOf( '\ServiceRegistry\RegistryInterface', $this->newInstance() );
	}

	/**
	 * @since 0.1
	 */
	public function testConstructStaticInstance() {

		$instance = ServiceRegistry::getInstance();

		$this->assertInstanceOf( '\ServiceRegistry\RegistryInterface', $instance );
		$this->assertTrue( $instance === ServiceRegistry::getInstance() );

		ServiceRegistry::reset();

		$this->assertFalse( $instance === ServiceRegistry::getInstance() );
	}

	/**
	 * @since 0.1
	 */
	public function testConstructSeparateStaticInstanceInvocation() {

		$object = function() {
			return new \stdClass;
		};

		ServiceRegistry::getInstance( 'foo' )->registerObject( 'Foo', $object );
		ServiceRegistry::getInstance( 'bar' )->registerObject( 'Foo', $object );

		$this->assertTrue( ServiceRegistry::getInstance( 'foo' ) === ServiceRegistry::getInstance( 'foo' ) );
		$this->assertFalse( ServiceRegistry::getInstance( 'foo' ) === ServiceRegistry::getInstance( 'bar' ) );

		ServiceRegistry::reset();
	}

	/**
	 * @since 0.1
	 */
	public function testRegisterObjectAsPrototype() {

		$object = function() {
			return new \stdClass;
		};

		$instance = $this->newInstance();
		$instance->registerObject( 'Foo', $object );

		$this->assertTrue( $instance->hasObject( 'Foo' ) );
		$this->assertInstanceOf( '\stdClass', $instance->newObject( 'Foo' ) );
		$this->assertFalse( $instance->newObject( 'Foo' ) === $instance->newObject( 'Foo' ) );

	}

	/**
	 * @since 0.1
	 */
	public function testRegisterObjectAsSingleton() {

		$object = function() {
			return new \stdClass;
		};

		$instance = $this->newInstance();
		$instance->registerObject( 'Foo', $object, 'singleton' );

		$this->assertInstanceOf( '\stdClass', $instance->newObject( 'Foo' ) );
		$this->assertTrue( $instance->newObject( 'Foo' ) === $instance->newObject( 'Foo' ) );

	}

	/**
	 * @since 0.1
	 */
	public function testRegisterInvalidObjectSetOffInvalidArgumentException() {

		$this->setExpectedException( 'InvalidArgumentException' );

		$object = function() {
			return new \stdClass;
		};

		$instance = $this->newInstance();
		$instance->registerObject( 'Foo', $object );

		$instance->newObject( array( 'Foo' ) );

	}

	/**
	 * @since 0.1
	 */
	public function testRegisterInvalidKeySetOffInvalidArgumentException() {

		$this->setExpectedException( 'InvalidArgumentException' );

		$object = function() {
			return new \stdClass;
		};

		$this->newInstance()->registerObject( $object, null );

	}

	/**
	 * @since 0.1
	 */
	public function testRegisterInvalidObjectSignatureSetOffInvalidArgumentException() {

		$this->setExpectedException( 'InvalidArgumentException' );

		$this->newInstance()->registerObject( 'Foo', null );

	}

	/**
	 * @since 0.1
	 */
	public function testRegisterObjectWithArgument() {

		$object = function( $builder ) {

			$instance = new \stdClass;
			$instance->foo = $builder->newObject( 'Bar' );

			return $instance;
		};

		$instance = $this->newInstance();
		$instance->registerObject( 'Foo', $object );

		$newObject = $instance->newObject( 'Foo', array(
			'Bar' => 'FooBar'
		) );

		$this->assertTrue( $instance->hasObject( 'Bar' ) );
		$this->assertFalse( $instance->hasObject( 'FooBar' ) );

		$this->assertInstanceOf( '\stdClass', $newObject );
		$this->assertEquals( 'FooBar', $newObject->foo );

	}

	/**
	 * @since 0.1
	 */
	public function testRegisterObjectWithInvalidArgumentSetOffInvalidArgumentException() {

		$this->setExpectedException( 'InvalidArgumentException' );

		$object = function( $builder ) {

			$instance = new \stdClass;
			$instance->foo = $builder->newObject( 'Bar' );

			return $instance;
		};

		$instance = $this->newInstance();
		$instance->registerObject( 'Foo', $object );

		$instance->newObject( 'Foo', array(
			new \stdClass
		) );

	}

	/**
	 * @since 0.1
	 */
	public function testHasObjectWithInvalidArgumentSetOffInvalidArgumentException() {

		$this->setExpectedException( 'InvalidArgumentException' );

		$this->newInstance()->hasObject( new \stdClass );

	}


	/**
	 * @since 0.1
	 */
	public function testRegisterObjectWithMissingObjectSetOffRuntimeException() {

		$this->setExpectedException( 'RuntimeException' );

		$object = function( $builder ) {

			$instance = new \stdClass;
			$instance->foo = $builder->newObject( 'Bar' );

			return $instance;
		};

		$instance = $this->newInstance();
		$instance->registerObject( 'Foo', $object );

		$instance->newObject( 'Foo' );

	}

	/**
	 * @since 0.1
	 */
	public function testGetAllServices() {

		$object = function() {
			return new \stdClass;
		};

		$instance = $this->newInstance();
		$instance->registerObject( 'FooMan', $object );
		$instance->registerObject( 'FanMu', $object );

		$this->assertEquals(
			array( 'FooMan', 'FanMu' ),
			array_keys( $instance->getAllServices() )
		);

	}

	/**
	 * @since 0.1
	 */
	public function testRegisterContainerUsingAServiceContainer() {

		$definition = function( $registry ) {

			$registry->registerObject( 'Foo', function ( $registry ) {
				return new \stdClass;
			} );

			$registry->registerObject( 'Bar', function ( $registry ) {
				return $registry->newObject( 'Foo' );
			}, 'singleton' );

		};

		$container = $this->newServiceContainer( $definition );

		$this->assertRegisteredContainer( $this->newInstance( $container ) );

		$instance = $this->newInstance();
		$instance->registerContainer( $container );

		$this->assertRegisteredContainer( $instance );
	}

	/**
	 * @since 0.1
	 */
	public function assertRegisteredContainer( ServiceRegistry $instance ) {

		$this->assertInstanceOf( '\stdClass', $instance->newObject( 'Foo' ) );
		$this->assertInstanceOf( '\stdClass', $instance->newObject( 'Bar' ) );

		$this->assertTrue(
			$instance->newObject( 'Foo' ) !== $instance->newObject( 'Foo' ),
			'Asserts that newObject() returns different instances'
		);

		$this->assertTrue(
			$instance->newObject( 'Foo' ) !== $instance->newObject( 'Bar' ),
			'Asserts that newObject() returns different instances'
		);

		$this->assertTrue(
			$instance->newObject( 'Bar' ) === $instance->newObject( 'Bar' ),
			'Asserts that newObject() returns the same instance (singleton)'
		);

	}

	/**
	 * @since 0.1
	 */
	public function testRegisterContainerWithInvalidContainerSetOffRuntimeException() {

		$this->setExpectedException( 'RuntimeException' );

		$this->newInstance()->registerContainer( $this->newServiceContainer( 'Foo' ) );

	}

	/**
	 * @since 0.1
	 */
	public function testCircularReferenceDetectionSetOffOutOfBoundsException() {

		$this->setExpectedException( 'OutOfBoundsException' );

		$instance = $this->newInstance();
		$instance->registerObject( 'Foo', function( $registry ) {
			return $registry->newObject( 'Foo' );
		} );

		$instance->newObject( 'Foo' );

	}

}
