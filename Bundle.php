<?php
namespace Asgard\Translation;

/**
 * The tester bundle.
 *
 * @author Michel Hognerud <michel@hognerud.net>
*/
class Bundle extends \Asgard\Core\BundleLoader {
	public function run(\Asgard\Container\ContainerInterface $container) {
		if($container->has('console')) {

			$translator = $container['translator'];
			$root = $container['kernel']['root'];
			$dir = $container['config']['translation.directories'];
			if(!$dir)
				$dir = [$root.'/app'];

			$container['errorHandler']->ignoreDir($root.'/vendor/nikic/php-parser/lib/PhpParser');

			$exportCsvCommand = new ExportCsvCommand($translator, $dir);
			$container['console']->add($exportCsvCommand);

			$importCommand = new ImportCommand();
			$container['console']->add($importCommand);

			$exportYamlCommand = new ExportYamlCommand($translator, $dir);
			$container['console']->add($exportYamlCommand);
		}
	}
}