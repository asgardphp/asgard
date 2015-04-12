<?php
namespace Asgard\Tester;

/**
 * The tester bundle.
 *
 * @author Michel Hognerud <michel@hognerud.net>
*/
class Bundle extends \Asgard\Core\BundleLoader {
	public function run(\Asgard\Container\ContainerInterface $container) {
		if($container->has('console')) {
			$httpKernel = $container['httpKernel'];
			$resolver = $container['resolver'];
			$db = $container['db'];
			$mm = $container['migrationManager'];

			$runCommand = new \Asgard\Tester\RunCommand($httpKernel, $resolver, $db, $mm);
			$container['console']->add($runCommand);

			$curlCommand = new \Asgard\Tester\CurlCommand();
			$container['console']->add($curlCommand);

			$config = $container['config']['tester.coverage'];
			if(!is_array($config))
				$config = [];
			if(!isset($config['include'])) {
				$config['include'] = [
					$container['kernel']['root'].'/app/'
				];
			}
			if(!isset($config['exclude'])) {
				$config['exclude'] = [
					$container['kernel']['root'].'/app/Kernel.php',
					$container['kernel']['root'].'/app/helpers.php',
					$container['kernel']['root'].'/app/bootstrap_all.php',
					$container['kernel']['root'].'/app/bootstrap_prod.php',
					$container['kernel']['root'].'/app/bootstrap_dev.php',
					$container['kernel']['root'].'/app/bootstrap_test.php',
					$container['kernel']['root'].'/app/Logger.php',
				];
			}
			$coverageCommand = new \Asgard\Tester\CoverageCommand($config);
			$container['console']->add($coverageCommand);
		}
	}
}