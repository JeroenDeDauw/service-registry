[![Build Status](https://travis-ci.org/mwjames/service-registry.png?branch=master)](https://travis-ci.org/mwjames/service-registry)

A minimalistic service and dependency injection library allowing service definitions being loaded from a container or directly registered with an instance.

## Installation
The recommended way to install this extension is through [Composer][composer]. Just add the following to the MediaWiki ``composer.json`` file and run the ``php composer.phar install/update`` command.

```json
{
	"require": {
		"mwjames/service-registry": "dev-master"
	},
	"repositories": [
		{
			"type": "vcs",
			"url":  "git@github.com:mwjames/service-registry.git"
		}
	],
	"minimum-stability" : "dev"
}
```
## Example
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
 * Register service container
 */
ServiceRegistry::getInstance()->registerContainer( new FooContainer() );

/**
 * Create new 'Foo' prototype instance
 */
$fooInstance = ServiceRegistry::getInstance()->newObject( 'Foo' );

/**
 * Create new 'Bar' singleton instance
 */
$barInstance = ServiceRegistry::getInstance()->newObject( 'Bar' );

/**
 * Register service defintion
 */
ServiceRegistry::getInstance()->registerObject( 'Foz', function( $registry ) {
	return new \stdClass;
} );

$fozInstance = ServiceRegistry::getInstance()->newObject( 'Foz' );

```

[composer]: http://getcomposer.org/

