<%
namespace <?=$bundle['namespace'] ?>;

class Bundle extends \Asgard\Core\BundleLoader {
	public function run(\Asgard\Container\Container $container) {
		parent::run($container);

		<?php $container['hooks']->trigger('Asgard.Core.Generate.bundlephp', [$bundle]) ?>
	}
}