<?php
namespace Coxis\Hook;

class Bundle extends \Coxis\Core\BundleLoader {
	public function load($queue) {
		\Coxis\Core\Autoloader::preloadDir(dirname(__FILE__));
		
		parent::load($queue);
	}
}