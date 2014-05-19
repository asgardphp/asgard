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
		$translator = new \Asgard\Translation\Translator;
		$translator->setLocale('fr');
		$translator->importLocales(realpath(__DIR__.'/locales/'));
		$this->assertEquals('Bonjour Michel !', $translator->trans('Hello :name!', array('name' => 'Michel')));
	}
}
?>