[![Build Status](https://travis-ci.org/mwjames/service-registry.png?branch=master)](https://travis-ci.org/mwjames/service-registry)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/mwjames/service-registry/badges/quality-score.png?s=adf2e12799727916defd556045e4c6da766501dd)](https://scrutinizer-ci.com/g/mwjames/service-registry/)
[![Code Coverage](https://scrutinizer-ci.com/g/mwjames/service-registry/badges/coverage.png?s=14dacb9b418c90512e427e8cbfdeb21aee2ff0ea)](https://scrutinizer-ci.com/g/mwjames/service-registry/)

A minimalistic service and dependency injection library allowing services being loaded from a container or being directly registered with an instance.
* Support for shared and separate instance invocation
* Support for prototypical and singleton scope during object invocation
* Functionality to register multiple container
* Functionality to detect circular references within an object graph

## Installation
The recommended way to install this library is through [Composer][composer]. Just add the following to your ``composer.json`` file and run the ``php composer.phar install/update`` command.

```json
{
	"require": {
		"mwjames/service-registry": "~0.1"
	},
	"repositories": [
		{
			"type": "vcs",
			"url":  "https://github.com/mwjames/service-registry"
		}
	]
}
```
## Example
Register services directly with the registry instance.

```php
ServiceRegistry::getInstance()->registerObject( 'Foz', function( $registry ) {
	return new \stdClass;
} );

$foz = ServiceRegistry::getInstance()->newObject( 'Foz' );
```

Specify services using a `ServiceContainer` and register them with the `ServiceRegistry` instance at a convenient time.
```php
use ServiceRegistry\ServiceContainer;

class FooContainer implements ServiceContainer {

	/**
	 * @return Closure
	 */
	public function loadAllDefinitions() {

		return function( $registry ) {

			$registry->registerObject( 'Foo', function ( $registry ) {
				return new \stdClass;
			} );

			$registry->registerObject( 'Bar', function ( $registry ) {
				return $registry->newObject( 'Foo' );
			}, 'singleton' );

		};

	}

}

/**
 * Register a container with a static instance
 */
ServiceRegistry::getInstance()->registerContainer( new FooContainer() );

/**
 * Register a container with a non-static instance
 */
$instance = new ServiceRegistry( new FooContainer() );
```

Create a service instance from an injected container with a prototypical (default) scope where each injection will result in a new instance while the singleton scope will return the same instance over the lifetime of a request.
```php
/**
 * Create new 'Foo' prototype instance
 */
$fooInstance = ServiceRegistry::getInstance()->newObject( 'Foo' );

/**
 * Create new 'Bar' singleton instance
 */
$barInstance = ServiceRegistry::getInstance()->newObject( 'Bar' );
```

Shared and separate instance invocation allowing to share definitions amongst application or
uncouple definitions based on a given identifier (namespace, id, group etc.).
```php
/**
 * Shared instance
 */
ServiceRegistry::getInstance()->registerContainer( new FooContainer() );

/**
 * Separate 'foo' instance
 */
ServiceRegistry::getInstance( 'foo' )->registerContainer( new FooContainer() );
```
For more exhaustive examples, please consult the unit test.

[composer]: http://getcomposer.org/

