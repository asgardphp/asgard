<?php
namespace Asgard\Utils;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		$this->app->register('email', function($app, $args) {
			$args[3] = isset($args[3]) ? $args[3]:'';
			$args[4] = isset($args[4]) ? $args[4]:'';
			return new \Asgard\Utils\Email($args[0], $args[1], $args[2], $args[3], $args[4]); }
		);
		$this->app->register('memory', function() { return new \Asgard\Core\Memory; } );
		$this->app->register('js', function() { return new \Asgard\Utils\JS; });
		$this->app->register('html', function($app) { return new \Asgard\Utils\HTML($app['request']); });
		$this->app->register('paginator', function($app, $args) { return new \Asgard\Utils\Paginator($args[0], $args[1], $args[2], $app['request']); });
		
		parent::load($bundlesManager);
	}
}