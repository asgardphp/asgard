<?php
namespace Asgard\Utils;

class Bundle extends \Asgard\Core\BundleLoader {
	public function buildApp($app) {
		$app->register('js', function() { return new \Asgard\Utils\JS; });
		$app->register('paginator', function($app, $args) { return new \Asgard\Utils\Paginator($args[0], $args[1], $args[2], $app['request']); });
	}
}