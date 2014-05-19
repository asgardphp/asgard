<?php
namespace Asgard\Core;

class Kernel {
	protected $app;

	public static function setDefaultEnvironment() {
		if(!defined('_ENV_')) {
			if(PHP_SAPI == 'cli' || $_SERVER['HTTP_HOST'] == '127.0.0.1' || $_SERVER['HTTP_HOST'] == 'localhost')
				define('_ENV_', 'dev');
			else
				define('_ENV_', 'prod');
		}
	}

	public function __construct($app) {
		$this->app = $app;
	}

	public function load($bootstrap=true) {
		static::setDefaultEnvironment();

		$this->app['config'] = new \Asgard\Core\Config('config');
		$this->app['hook'] = new \Asgard\Hook\Hook($this->app);
		$this->app->register('cache', function($app) {
			$driver = $app['config']->get('cache_driver');
			if(!$driver)
				$driver = 'Asgard\Cache\NullCache';
			return $app->make($driver);
		});

		$app = $this->app;
		if($bootstrap) {
			if(file_exists(_DIR_.'app/bootstrap_'.strtolower(_ENV_).'.php'))
				include _DIR_.'app/bootstrap_'.strtolower(_ENV_).'.php';
			if(file_exists(_DIR_.'app/bootstrap_all.php'))
				include _DIR_.'app/bootstrap_all.php';
		}

		$bundles = $this->app['config']->get('bundles');
		$bundlesdirs = $this->app['config']->get('bundlesdirs');
		$bundlesmanager = new \Asgard\Core\BundlesManager;
		$bundlesmanager->setApp($this->app);
		$bundlesmanager->addBundles($bundles);
		$bundlesmanager->addBundlesDirs($bundlesdirs);
		$bundlesmanager->loadBundles();
		$this->app->set('bundlesmanager', $bundlesmanager);
	}
}