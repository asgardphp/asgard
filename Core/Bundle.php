<?php
namespace Asgard\Core;

/**
 * Asgard core bundle.
 */
class Bundle extends \Asgard\Core\BundleLoader {
	/**
	 * Register services.
	 * @param  \Asgard\Container\Container $container
	 */
	public function buildContainer(\Asgard\Container\Container $container) {
		#Db
		$container->register('schema', function($container) { return new \Asgard\Db\Schema($container['db']); } );
		$container->register('db', function($container) { return new \Asgard\Db\DB($container['config']['database']); } );

		#Email
		$container->register('email', function($container) {
			$emailDriver = '\\'.trim($container['config']['email.driver'], '\\');
			$email = new $emailDriver();
			$email->transport($container['config']['email']);
			return $email;
		});

		#Entity
		$container->register('entitiesmanager', function($container) {
			$entitiesManager = new \Asgard\Entity\EntitiesManager($container);
			$entitiesManager->setHooksManager($container['hooks']);
			$entitiesManager->setDefaultLocale($container['config']['locale']);
			$entitiesManager->setValidatorFactory($container->createFactory('validator'));
			return $entitiesManager;
		});
		
		#Form
		$container->register('widgetsManager', function() { return new \Asgard\Form\WidgetsManager; });
		$container->register('entityFieldsSolver', function() { return new \Asgard\Entityform\EntityFieldsSolver; });
		$container->register('entityForm', function($container, $entity, $params=[], $request=null) {
			if($request === null)
				$request = $container['httpKernel']->getRequest();
			$entityFieldsSolver = clone $container['entityFieldsSolver'];
			$form = new \Asgard\Entityform\EntityForm($entity, $params, $request, $entityFieldsSolver, $container['dataMapper']);
			$form->setWidgetsManager(clone $container['widgetsManager']);
			$form->setTranslator($container['translator']);
			$form->setContainer($container);
			return $form;
		});
		$container->register('form', function($container, $name=null, $params=[], $request=null, $fields=[]) {
			if($request === null)
				$request = $container['httpKernel']->getRequest();
			$form = new \Asgard\Form\Form($name, $params, $request, $fields);
			$form->setWidgetsManager(clone $container['widgetsManager']);
			$form->setTranslator($container['translator']);
			$form->setContainer($container);
			return $form;
		});

		#Hook
		$container->register('hooks', function($container) { return new \Asgard\Hook\HooksManager($container); } );

		#Http
		$container->register('httpKernel', function($container) {
			$httpKernel = new \Asgard\Http\HttpKernel($container);
			$httpKernel->setDebug($container['config']['debug']);
			if($container->registered('templateEngine'))
				$httpKernel->setTemplateEngineFactory($container->createFactory('templateEngine'));
			$httpKernel->setHooksManager($container['hooks']);
			$httpKernel->setErrorHandler($container['errorHandler']);
			$httpKernel->setTranslator($container['translator']);
			$httpKernel->setResolver($container['resolver']);
			$container['resolver']->setHttpKernel($httpKernel);
			return $httpKernel;
		});
		$container->register('resolver', function($container) {
			return new \Asgard\Http\Resolver($container['cache']);
		});
		$container->register('browser', function($container) {
			return new \Asgard\Http\Browser\Browser($container['httpKernel']);
		});
		$container->register('response', function() { return new \Asgard\Http\Response; } );
		$container->register('cookieManager', function() { return new \Asgard\Http\CookieManager; } );
		$container->register('html', function($container) { return new \Asgard\Http\Utils\HTML($container['httpKernel']->getRequest()); });
		$container->register('url', function($container) { return $container['httpKernel']->getRequest()->url; });

		#Migration
		$container->register('migrationsManager', function($container) {
			$mm = new \Asgard\Migration\MigrationsManager($container['kernel']['root'].'/migrations/', $container);
			if($container->has('db'))
				$mm->setDB($container['db']);
			if($container->has('schema'))
				$mm->setDB($container['schema']);
			return $mm;
		});

		#Common
		$container->register('paginator', function($container, $count, $page, $per_page) {
			return new \Asgard\Common\Paginator($count, $page, $per_page, $container['httpKernel']->getRequest());
		});

		#Validation
		$container->register('validator', function($container) {
			$validator = new \Asgard\Validation\Validator;
			$validator->setRegistry($container['rulesregistry']);
			return $validator;
		});
		$container->register('rulesregistry', function() { return new \Asgard\Validation\RulesRegistry; } );

		#ORM
		$container->register('orm', function($container, $entityClass, $dataMapper, $locale, $prefix) {
			return new \Asgard\Orm\ORM($entityClass, $dataMapper, $locale, $prefix, $container->createFactory('paginator'));
		});
		$container->register('collectionOrm', function($container, $entityClass, $name, $dataMapper, $locale, $prefix) {
			return new \Asgard\Orm\CollectionORM($entityClass, $name, $dataMapper, $locale, $prefix, $container->createFactory('paginator'));
		});
		$container->register('datamapper', function($container) {
			return new \Asgard\Orm\DataMapper(
				$container['entitiesManager'],
				$container['db'],
				$container['config']['locale'],
				$container['config']['database/prefix'],
				$container->createFactory('orm'),
				$container->createFactory('collectionOrm')
			);
		});
	}

	/**
	 * Run the bundle.
	 * @param  \Asgard\Container\Container $container
	 */
	public function run(\Asgard\Container\Container $container) {
		parent::run($container);

		#Files
		$container['rulesregistry']->registerNamespace('Asgard\File\Rules');

		#ORM
		$container['rulesregistry']->registerNamespace('Asgard\Orm\Rules');

		#Controllers Templates
		$container['httpKernel']->addTemplatePathSolver(function($controller, $template) {
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

		if($container->has('translator')) {
			foreach(glob($this->getPath().'/../Validation/translations/'.$container['translator']->getLocale().'/*') as $file)
				$container['translator']->addResource('yaml', $file, $container['translator']->getLocale());
			foreach(glob($this->getPath().'/../Form/translations/'.$container['translator']->getLocale().'/*') as $file)
				$container['translator']->addResource('yaml', $file, $container['translator']->getLocale());
		}

		if($container->has('console')) {
			$root = $container['kernel']['root'];

			$em = $container['entitiesManager'];
			$mm = $container['migrationsManager'];

			#if database is available
			try {
				$db = $container['db'];
				$dataMapper = $container['dataMapper'];

				$ormAutomigrate = new \Asgard\Orm\Commands\AutoMigrateCommand($em, $mm, $dataMapper);
				$container['console']->add($ormAutomigrate);

				$ormGenerateMigration = new \Asgard\Orm\Commands\GenerateMigrationCommand($em, $mm, $dataMapper);
				$container['console']->add($ormGenerateMigration);

				$dbRestore = new \Asgard\Db\Commands\RestoreCommand($db);
				$container['console']->add($dbRestore);

				$dbEmpty = new \Asgard\Db\Commands\EmptyCommand($db);
				$container['console']->add($dbEmpty);

				$dbDump = new \Asgard\Db\Commands\DumpCommand($db, $container['kernel']['root'].'/storage/dumps/sql');
				$container['console']->add($dbDump);
			} catch(\Exception $e) {}

			$httpRoutes = new \Asgard\Http\Commands\RoutesCommand($container['resolver']);
			$container['console']->add($httpRoutes);

			$containerServices = new \Asgard\Container\Commands\ListCommand($root);
			$container['console']->add($containerServices);

			$cacheClear = new \Asgard\Cache\Commands\ClearCommand($container['cache']);
			$container['console']->add($cacheClear);

			$configInit = new \Asgard\Config\Commands\InitCommand($container['kernel']['root'].'/config');
			$container['console']->add($configInit);

			$dbInit = new \Asgard\Db\Commands\InitCommand($container['kernel']['root'].'/config');
			$container['console']->add($dbInit);

			$migrationMigrate = new \Asgard\Migration\Commands\MigrateCommand($container['kernel']['root'].'/migrations');
			$container['console']->add($migrationMigrate);

			$migrationList = new \Asgard\Migration\Commands\ListCommand($container['kernel']['root'].'/migrations');
			$container['console']->add($migrationList);

			$migrationMigrateOne = new \Asgard\Migration\Commands\MigrateOneCommand($container['kernel']['root'].'/migrations');
			$container['console']->add($migrationMigrateOne);

			$migrationRefresh = new \Asgard\Migration\Commands\RefreshCommand($container['kernel']['root'].'/migrations');
			$container['console']->add($migrationRefresh);

			$migrationRemove = new \Asgard\Migration\Commands\RemoveCommand($container['kernel']['root'].'/migrations');
			$container['console']->add($migrationRemove);

			$migrationRollback = new \Asgard\Migration\Commands\RollbackCommand($root.'/migrations');
			$container['console']->add($migrationRollback);

			$migrationUnmigrate = new \Asgard\Migration\Commands\UnmigrateCommand($root.'/migrations');
			$container['console']->add($migrationUnmigrate);

			$migrationAdd = new \Asgard\Migration\Commands\AddCommand($root.'/migrations');
			$container['console']->add($migrationAdd);

			$httpTests = new \Asgard\Http\Commands\GenerateTestsCommand($container['kernel']['root'].'/tests');
			$container['console']->add($httpTests);

			$httpBrowser = new \Asgard\Http\Commands\BrowserCommand();
			$container['console']->add($httpBrowser);
		}
	}
}