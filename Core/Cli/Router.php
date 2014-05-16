<?php
namespace Asgard\Core\Cli;

class Router {
	public function addRoutes($routes) {
		foreach($routes as $route)
			$this->addRoute($route['shortcut'], array($route['controller'], $route['action']), $route['usage'], $route['description']);
	}

	public function addRoute($route, $action, $usage='', $description='') {
		$this->routes[$route]['route'] = $action;
		$this->routes[$route]['usage'] = $usage;
		$this->routes[$route]['description'] = $description;
	}

	public $routes = array(
		#move it to git
		//~ 'init'	=>	'asgard:test',
		#git clone https://leyou@bitbucket.org/leyou/asgard.git
		//~ git clone ...
		'get'	=>	array(
			'usage'	=>	'get [relative_url]',
			'description'	=>	'Make a GET request on an url',
			'route'	=>	array('Asgard\Core\Cli\AsgardController', 'get'),	#set config var
		),
		// 'set'	=>	array(
		// 	'usage'	=>	'set ',
		// 	'route'	=>	array('Asgard\Core\Cli\AsgardController', 'set'),	#set config var
		// ),
		//~ 'search'	=>	'asgard:test',	#search for bundles
		'import'	=>	array(
			'usage'	=>	'import bundle',
			'description'	=>	'Import a new bundle',
			'route'	=>	array('Asgard\Core\Cli\AsgardController', 'import'),	#import bundle
		),
		'build'	=>	array(
			'usage'	=>	'build build.yml',
			'description'	=>	'Build bundles from a yml file',
			'route'	=>	array('Asgard\Core\Cli\AsgardController', 'build'),	#build bundles from build.yml
		),
		'install'	=>	array(
			'usage'	=>	'install bundle',
			'description'	=>	'Install a bundle',
			'route'	=>	array('Asgard\Core\Cli\AsgardController', 'install'),	
		),
		// 'install-all'	=>	array(
		// 	'usage'	=>	'installAll',
		// 	'description'	=>	'Install all bundles',
		// 	'route'	=>	array('Asgard\Core\Cli\AsgardController', 'installAll'),	
		// ),
		'console'	=>	array(
			'usage'	=>	'console',
			'description'	=>	'Open a PHP console',
			'route'	=>	array('Asgard\Core\Cli\AsgardController', 'console'),	
		),
		'publish'	=>	array(
			'usage'	=>	'publish [bundle]',
			'description'	=>	'Publish a bundle assets',
			'route'	=>	array('Asgard\Core\Cli\AsgardController', 'publish'),
		),

		'generate-tests'	=>	array(
			'usage'	=>	'generate-tests',
			'description'	=>	'Generate an AutoTest file to tests your application',
			'route'	=>	array('Asgard\Core\Cli\AsgardController', 'generateTests'),
		),
		'generate-testsuite'	=>	array(
			'usage'	=>	'generate-testsuite [output.xml]',
			'description'	=>	'Generate a testsuite file for testing all your bundles with phpunit',
			'route'	=>	array('Asgard\Core\Cli\AsgardController', 'generateTestSuite'),
		),
		
		'dump'	=>	array(
			'usage'	=>	'dump output.yml',
			'description'	=>	'Dump your database into a yml file',
			'route'	=>	array('Asgard\Core\Cli\DBController', 'dump'),	#dump data into data.yml
		),
		'backup-db'	=>	array(
			'usage'	=>	'backup',
			'description'	=>	'Dump DB into a timestamp named file',
			'route'	=>	array('Asgard\Core\Cli\DBController', 'backup'),	#dump data into default yml file
		),
		'backup-files'	=>	array(
			'usage'	=>	'backup-files',
			'description'	=>	'Backup the upload folder',
			'route'	=>	array('Asgard\Core\Cli\DBController', 'backupFiles'),	#dump data into default yml file
		),
		'load-all'	=>	array(
			'usage'	=>	'load-all',
			'description'	=>	'Load all bundles data',
			'route'	=>	array('Asgard\Core\Cli\DBController', 'loadAll'),	#load all data (including bundles), usually for startup
		),
		'load'	=>	array(
			'usage'	=>	'load',
			'description'	=>	'Load a specific data file',
			'route'	=>	array('Asgard\Core\Cli\DBController', 'load'),	#load specific data file
		),
		'version'	=>	array(
			'usage'	=>	'version',
			'description'	=>	'Give asgard version',
			'route'	=>	array('Asgard\Core\Cli\AsgardController', 'version'),
		),
		'cc'	=>	array(
			'usage'	=>	'cc',
			'description'	=>	'Clear cache',
			'route'	=>	array('Asgard\Core\Cli\AsgardController', 'cc'),
		),
	);

	public function run($controller, $action, $params=array()) {
		$c = new $controller;
		$c->run($action, $params);
	}

	public function dispatch($route, $args=array()) {
		if(!isset($this->routes[$route]['route']))
			return false;

		$route = $this->routes[$route]['route'];
		$controller = $route[0];
		$action = $route[1];

		$this->run($controller, $action, $args);

		return true;
	}
}