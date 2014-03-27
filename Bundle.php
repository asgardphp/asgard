<?php
namespace Asgard\Form;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load($queue) {
		if(\Asgard\Core\App::get('autoloader'))
			\Asgard\Core\App::get('autoloader')->preloadDir(__dir__);

		parent::load($queue);
	}
}