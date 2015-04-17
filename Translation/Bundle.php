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

			$translationResources = $container['translationResources'];
			$translator = $container['translator'];
			$root = $container['kernel']['root'];
			$dir = $container['config']['translation.directories'];
			if(!$dir)
				$dir = [$root.'/app'];

			$container['errorHandler']->ignoreDir($root.'/vendor/nikic/php-parser/lib/PhpParser');

			$exportCsvCommand = new ExportCsvCommand($translationResources, $translator, $dir);
			$container['console']->add($exportCsvCommand);

			$importCommand = new ImportCommand();
			$container['console']->add($importCommand);

			$exportYamlCommand = new ExportYamlCommand($translationResources, $translator, $dir);
			$container['console']->add($exportYamlCommand);
		}
	}
}