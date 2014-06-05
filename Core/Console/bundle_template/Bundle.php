<%
namespace <?php echo $bundle['namespace'] ?>;

class Bundle extends \Asgard\Core\BundleLoader {
	public function run() {
		parent::run();

		<?php $app['hooks']->trigger('Asgard.Core.Generate.bundlephp', array($bundle)) ?>
	}
}