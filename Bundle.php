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

			$runCommand = new \Asgard\Migration\Commands\RunCommand($httpKernel, $resolver, $db, $mm);
			$container['console']->add($runCommand);

			$curlCommand = new \Asgard\Migration\Commands\CurlCommand();
			$container['console']->add($curlCommand);
		}
	}
}