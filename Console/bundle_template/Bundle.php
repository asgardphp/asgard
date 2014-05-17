<%
namespace <?php echo $bundle['namespace'] ?>;

class Bundle extends \Asgard\Core\BundleLoader {
	public function run() {
		<?php \Asgard\Core\App::get('hook')->trigger('Agard\CLI\generator\bundle.php', array($bundle)) ?>
		parent::run();
	}
}