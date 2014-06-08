<?php
namespace Asgard\Core;

class Bundle extends \Asgard\Core\BundleLoader {
	public function buildApp($app) {
		#Db
		$app->register('schema', function($app) { return new \Asgard\Db\Schema($app['db']); } );
		$app->register('db', function($app) { return new \Asgard\Db\DB($app['config']['database']); } );

		#Email
		$app->register('email', function($app) {
			$emailDriver = '\\'.trim($app['config']['email.driver'], '\\');
			$email = new $emailDriver();
			$email->transport($app['config']['email']);
			return $email;
		});

		#Entity
		$app->register('entitiesmanager', function($app) { return new \Asgard\Entity\EntitiesManager($app); } );
		
		#Form
		$app->register('form', function($app, $args) {
			if(!isset($args[0]))
				$args[0] = null;
			if(!isset($args[1]))
				$args[1] = array();
			if(!isset($args[2]))
				$args[2] = array();
			if(!isset($args[3]))
				$args[3] = null;
			if(!isset($args[4]))
				$args[4] = $app;
			return new \Asgard\Form\Form($args[0], $args[1], $args[2], $args[3], $args[4]);
		});

		#Hook
		$app->register('hooks', function($app) { return new \Asgard\Hook\HooksManager($app); } );

		#Http
		$app->register('httpKernel', function($app) { return new \Asgard\Http\HttpKernel($app); } );
		$app->register('resolver', function($app) {
			$resolver = new \Asgard\Http\Resolver($app['cache']);
			$resolver->setHttpKernel($app['httpKernel']);
			return $resolver;
		});
		$app->register('response', function() { return new \Asgard\Http\Response; } );
		$app->register('cookieManager', function() { return new \Asgard\Http\CookieManager; } );
		$app->register('html', function($app) { return new \Asgard\Http\Utils\HTML($app['request']); });
		$app->register('url', function($app) { return $app['request']->url; });

		#Migration
		$app->register('migrationsManager', function($app) {
			return new \Asgard\Migration\MigrationsManager($app['db'], $app['bundlesManager']);
		});

		#Common
		$app->register('paginator', function($app, $args) { return new \Asgard\Utils\Paginator($args[0], $args[1], $args[2], $app['request']); });

		#Validation
		$app->register('validator', function() { return new \Asgard\Validation\Validator; } );
		$app->register('rulesregistry', function() { return \Asgard\Validation\RulesRegistry::getInstance(); } );
	}

	public function run($app) {
		#Entity
		\Asgard\Entity\Entity::setApp($app);

		#Files
		$app['rulesregistry']->registerNamespace('Asgard\Files\Rules');

		#ORM
		$app['rulesregistry']->registerNamespace('Asgard\Orm\Rules');

		parent::run($app);
	}
}