[![Build Status](https://travis-ci.org/mwjames/service-registry.png?branch=master)](https://travis-ci.org/mwjames/service-registry)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/mwjames/service-registry/badges/quality-score.png?s=adf2e12799727916defd556045e4c6da766501dd)](https://scrutinizer-ci.com/g/mwjames/service-registry/)
[![Code Coverage](https://scrutinizer-ci.com/g/mwjames/service-registry/badges/coverage.png?s=14dacb9b418c90512e427e8cbfdeb21aee2ff0ea)](https://scrutinizer-ci.com/g/mwjames/service-registry/)

A minimalistic service and dependency injection library allowing service definitions being loaded from a container or directly registered with an instance.

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
Register service definitions directly with the registry instance.

```php
ServiceRegistry::getInstance()->registerObject( 'Foz', function( $registry ) {
	return new \stdClass;
} );

$foz = ServiceRegistry::getInstance()->newObject( 'Foz' );
```

Specify definitions using a service container and register them with the `ServiceRegistry` instance.
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

ServiceRegistry::getInstance()->registerContainer( new FooContainer() );
```

Create service instances from an injected container.
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

[composer]: http://getcomposer.org/

