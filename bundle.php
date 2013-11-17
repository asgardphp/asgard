<?php
namespace Coxis\Value;

class Bundle extends \Coxis\Core\BundleLoader {
	public function run() {
		\Coxis\Admin\Libs\AdminMenu::instance()->menu[8] = array('label' => 'Configuration', 'link' => '#', 'childs' => array(
			array('label' => 'Preferences', 'link' => 'preferences'),
			array('label' => __('Administrators'), 'link' => 'administrators'),
		));
		parent::run();
	}
}
return new Bundle;