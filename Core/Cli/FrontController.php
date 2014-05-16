<?php
namespace Asgard\Core\Cli;

class FrontController extends CLIController {
	public function mainAction($request) {
		$request = Request::createFromGlobals();
		
		if(!$request->getRoute()) {
			\Asgard\Core\App::loadDefaultApp();
			static::usage();
		}

		if(!defined('_ENV_'))
			define('_ENV_', $request->getEnvironment());

		/* CONFIG */
		\Asgard\Core\App::loadDefaultApp();
		\Asgard\Core\App::get('config')->loadConfigDir('config');
		
		if(!\Asgard\Core\App::get('clirouter')->dispatch($request->getRoute(), $request->args->all()))
			static::usage();
	}
	
	public static function usage() {
		echo 'Usage: '."\n";
		foreach(\Asgard\Core\App::get('clirouter')->routes as $name=>$route) {
			echo $name;
			if(isset($route['usage']) && $route['usage'])
				echo ": ".$route['usage'];
			echo "\n";
			if(isset($route['description']) && $route['description'])
				echo "    ".$route['description']."\n";
		}
		die();
	}
}