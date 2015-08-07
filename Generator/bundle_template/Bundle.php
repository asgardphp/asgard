<%
namespace <?=$bundle['namespace'] ?>;

class Bundle extends \Asgard\Core\BundleLoader {
	public function run(\Asgard\Container\ContainerInterface $container) {
		parent::run($container);

		<?php $this->generateFragment('bundle') ?>
	}
}