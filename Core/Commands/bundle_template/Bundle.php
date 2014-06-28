<%
namespace <?=$bundle['namespace'] ?>;

class Bundle extends \Asgard\Core\BundleLoader {
	public function run($app) {
		parent::run($app);

		<?php $app['hooks']->trigger('Asgard.Core.Generate.bundlephp', [$bundle]) ?>
	}
}