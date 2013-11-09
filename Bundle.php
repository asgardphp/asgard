<?php
namespace Coxis\Form;

class Bundle extends BundleLoader {
	public function load($queue) {
		Autoloader::preloadDir(dirname(__FILE__));
	}
}
return new Bundle;