<?php

/**
 * Initialization
 *
 * @author mwjames
 */

if ( defined( 'SERVICEREGISTRY_VERSION' ) ) {
	return 1;
}

define( 'SERVICEREGISTRY_VERSION', '0.1' );

if ( defined( 'MEDIAWIKI' ) ) {

	$GLOBALS['wgExtensionCredits']['other'][] = array(
		'path'        => __DIR__,
		'name'        => 'ServiceRegistry',
		'version'     => SERVICEREGISTRY_VERSION,
		'author'      => array( 'mwjames' ),
		'url'         => 'https://github.com/mwjames/service-registry',
		'description' => 'Minimalistic service and dependency injection library',
	);

}