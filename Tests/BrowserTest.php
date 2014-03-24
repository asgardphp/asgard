<?php
namespace Asgard\Utils\Tests;

class BrowserTest extends \PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
		require_once(_CORE_DIR_.'core.php');
		\Asgard\Core\App::instance(true)->config->set('bundles', array(
			_ASGARD_DIR_.'core',
			'app',
		));
		\Asgard\Core\App::loadDefaultApp();
	}
	
	public function test1() {
		$browser = new \Asgard\Utils\Browser;
		$doc = new \Asgard\Xpath\Doc($browser->get('')->content);
		$this->assertEquals($doc->text('//h1'), 'Asgard');
	}
}