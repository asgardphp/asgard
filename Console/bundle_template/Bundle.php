<%
namespace <?php echo $bundle['namespace'] ?>;

class Bundle extends \Asgard\Core\BundleLoader {
	public function run() {
		<?php $this->app['hook']->trigger('Agard\CLI\generator\bundle.php', array($bundle)) ?>
		parent::run();
	}
}