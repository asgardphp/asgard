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
		$app->register('entityFieldsSolver', function() { return new \Asgard\Form\EntityFieldsSolver; });
		$app->register('widgetsManager', function() { return new \Asgard\Form\WidgetsManager; });
		$app->register('entityForm', function($app, $entity, $params=[], $request=null) {
			$entityFieldsSolver = clone $app['entityFieldsSolver'];
			$form = new \Asgard\Form\EntityForm($entity, $params, $request, $entityFieldsSolver);
			$form->setWidgetsManager(clone $app['widgetsManager']);
			$form->setTranslator($app['translator']);
			$form->setHooks($app['hooks']);
			$form->setApp($app);
			return $form;
		});
		$app->register('form', function($app, $name=null, $params=[], $request=null, $fields=[]) {
			if($request === null)
				$request = $app['request'];
			$form = new \Asgard\Form\Form($name, $params, $request, $fields);
			$form->setWidgetsManager(clone $app['widgetsManager']);
			$form->setTranslator($app['translator']);
			$form->setApp($app);
			return $form;
		});

		#Hook
		$app->register('hooks', function($app) { return new \Asgard\Hook\HooksManager($app); } );

		#Http
		$app->register('httpKernel', function($app) {
			$httpKernel = new \Asgard\Http\HttpKernel($app);
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
			return new \Asgard\Migration\MigrationsManager($app['kernel']['root'].'/migrations/', $app);
		});

		#Common
		$app->register('paginator', function($app, $page, $per_page, $total) { return new \Asgard\Common\Paginator($page, $per_page, $total, $app['request']); });

		#Validation
		$app->register('validator', function() { return new \Asgard\Validation\Validator; } );
		$app->register('rulesregistry', function() { return \Asgard\Validation\RulesRegistry::getInstance(); } );
	}

	public function run($app) {
		parent::run($app);

		#Entity
		\Asgard\Entity\Entity::setApp($app);

		#Files
		$app['rulesregistry']->registerNamespace('Asgard\File\Rules');

		#ORM
		$app['rulesregistry']->registerNamespace('Asgard\Orm\Rules');

		#Controllers Templates
		$app['httpKernel']->addTemplatePathSolver(function($controller, $template) {
			if(!$controller instanceof \Asgard\Http\LambdaController) {
				$r = new \ReflectionClass($controller);
				$controllerName = basename(str_replace('\\', DIRECTORY_SEPARATOR, get_class($controller)));
				$controllerName = strtolower(preg_replace('/Controller$/i', '', $controllerName));

				$format = $controller->request->format();

				$file = realpath(dirname($r->getFileName()).'/../'.$format.'/'.$controllerName.'/'.$template.'.php');
				if(!file_exists($file))
					return realpath(dirname($r->getFileName()).'/../html/'.$controllerName.'/'.$template.'.php');
				else
					return $file;
			}
		});

		if($app->has('translator')) {
			foreach(glob($this->getPath().'/../Validation/translations/'.$app['translator']->getLocale().'/*') as $file)
				$app['translator']->addResource('yaml', $file, $app['translator']->getLocale());
			foreach(glob($this->getPath().'/../Form/translations/'.$app['translator']->getLocale().'/*') as $file)
				$app['translator']->addResource('yaml', $file, $app['translator']->getLocale());
		}

		if($app->has('console')) {
			$root = $app['kernel']['root'];

			$em = $app['entitiesManager'];
			$mm = $app['migrationsManager'];

			$ormAutomigrate = new \Asgard\Orm\Commands\AutoMigrateCommand($em, $mm, $app['db']);
			$app['console']->add($ormAutomigrate);

			$ormGenerateMigration = new \Asgard\Orm\Commands\GenerateMigrationCommand($em, $mm, $app['db']);
			$app['console']->add($ormGenerateMigration);

			$dbRestore = new \Asgard\Db\Commands\RestoreCommand($app['db']);
			$app['console']->add($dbRestore);

			$httpRoutes = new \Asgard\Http\Commands\RoutesCommand($app['resolver']);
			$app['console']->add($httpRoutes);

			$containerServices = new \Asgard\Container\Commands\ListCommand($root);
			$app['console']->add($containerServices);

			$cacheClear = new \Asgard\Cache\Commands\ClearCommand($app['cache']);
			$app['console']->add($cacheClear);

			$dbEmpty = new \Asgard\Db\Commands\EmptyCommand($app['db']);
			$app['console']->add($dbEmpty);

			$dbDump = new \Asgard\Db\Commands\DumpCommand($app['db'], $app['kernel']['root'].'/storage/dumps/sql');
			$app['console']->add($dbDump);

			$configInit = new \Asgard\Config\Commands\InitCommand($app['kernel']['root'].'/config');
			$app['console']->add($configInit);

			$dbInit = new \Asgard\Db\Commands\InitCommand($app['kernel']['root'].'/config');
			$app['console']->add($dbInit);

			$migrationMigrate = new \Asgard\Migration\Commands\MigrateCommand($app['kernel']['root'].'/migrations');
			$app['console']->add($migrationMigrate);

			$migrationList = new \Asgard\Migration\Commands\ListCommand($app['kernel']['root'].'/migrations');
			$app['console']->add($migrationList);

			$migrationMigrateOne = new \Asgard\Migration\Commands\MigrateOneCommand($app['kernel']['root'].'/migrations');
			$app['console']->add($migrationMigrateOne);

			$migrationRefresh = new \Asgard\Migration\Commands\RefreshCommand($app['kernel']['root'].'/migrations');
			$app['console']->add($migrationRefresh);

			$migrationRemove = new \Asgard\Migration\Commands\RemoveCommand($app['kernel']['root'].'/migrations');
			$app['console']->add($migrationRemove);

			$migrationRollback = new \Asgard\Migration\Commands\RollbackCommand($root.'/migrations');
			$app['console']->add($migrationRollback);

			$migrationUnmigrate = new \Asgard\Migration\Commands\UnmigrateCommand($root.'/migrations');
			$app['console']->add($migrationUnmigrate);

			$migrationAdd = new \Asgard\Migration\Commands\AddCommand($root.'/migrations');
			$app['console']->add($migrationAdd);

			$httpTests = new \Asgard\Http\Commands\GenerateTestsCommand($app['kernel']['root'].'/Tests');
			$app['console']->add($httpTests);

			$httpBrowser = new \Asgard\Http\Commands\BrowserCommand();
			$app['console']->add($httpBrowser);
		}
	}
}