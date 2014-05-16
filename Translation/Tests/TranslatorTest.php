<?php
namespace Asgard\Translation\Tests;

class TranslatorTest extends \PHPUnit_Framework_TestCase {
	// public static function setUpBeforeClass() {
	// 	if(!defined('_ENV_'))
	// 		define('_ENV_', 'test');
	// 	require_once _VENDOR_DIR_.'autoload.php';
	// 	\Asgard\Core\App::instance(true)->config->set('bundles', array(
	// 		new \Asgard\Utils\Bundle,
	// 	));
	// 	\Asgard\Core\App::loadDefaultApp();
	// }

	#translation
	public function test1() {
		\Asgard\Core\App::get('translator')->setLocale('fr');
		\Asgard\Core\App::get('translator')->importLocales(realpath(__dir__.'/locales/'));
		$this->assertEquals(__('Hello :name!', array('name' => 'Michel')), 'Bonjour Michel !');
	}
}
?>