<?php
namespace Asgard\Utils\Tests;

class LocaleTest extends \PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
		require_once(_CORE_DIR_.'core.php');
		\Asgard\Core\App::instance(true)->config->set('bundles', array(
			_ASGARD_DIR_.'core',
		));
		\Asgard\Core\App::loadDefaultApp();
	}

	#translation
	public function test1() {
		\Asgard\Core\App::get('locale')->setLocale('fr');
		\Asgard\Core\App::get('locale')->importLocales(realpath(__dir__.'/locales/'));
		$this->assertEquals(__('Hello :name!', array('name' => 'Michel')), 'Bonjour Michel !');
	}
}
?>