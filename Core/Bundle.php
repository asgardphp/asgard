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
		$app->register('widgetsManager', function() { return new \Asgard\Form\WidgetsManager(); });
		$app->register('entityForm', function($app, $entity, $params=[], $request=null) {
			$form = new \Asgard\Form\EntityForm($entity, $params, $request);
			$form->setWidgetsManager(clone $app['widgetsManager']);
			$form->setTranslator($app['translator']);
			$form->setHooks($app['hooks']);
			$form->setWidgetsManager($app['widgetsManager']);
			$form->setApp($app);
			return $form;
		});
		$app->register('form', function($app, $name=null, $params=[], $fields=[], $request=null) {
			$form = new \Asgard\Form\Form($name, $params, $fields, $request);
			$form->setWidgetsManager(clone $app['widgetsManager']);
			$form->setTranslator($app['translator']);
			$form->setHooks($app['hooks']);
			$form->setWidgetsManager($app['widgetsManager']);
			$form->setApp($app);
			return $form;
		});

		#Hook
		$app->register('hooks', function($app) { return new \Asgard\Hook\HooksManager($app); } );

		#Http
		$app->register('httpKernel', function($app) {
			$httpKernel = new \Asgard\Http\HttpKernel($app);
			$httpKernel->start($app['kernel']['root'].'/app/start.php');
			return $httpKernel;
		});
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
		$app->register('paginator', function($app, $page, $per_page, $total) { return new \Asgard\Common\Paginator($page, $per_page, $total, $app['request']); });

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

		if($app->has('translator')) {
			foreach(glob($this->getPath().'/../Validation/locales/'.$app['translator']->getLocale().'/*') as $file)
				$app['translator']->addResource('yaml', $file, $app['translator']->getLocale());
			foreach(glob($this->getPath().'/../Form/locales/'.$app['translator']->getLocale().'/*') as $file)
				$app['translator']->addResource('yaml', $file, $app['translator']->getLocale());
		}
	}
}