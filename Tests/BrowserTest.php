<?php
namespace Asgard\Utils\Tests;

class BrowserTest extends \PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
		require_once _VENDOR_DIR_.'autoload.php';
		\Asgard\Core\App::instance(true)->config
		->set('bundles', array(
			'app/general',
		))
		->set('bundlesdirs', array(
			// 'app'
		));
		\Asgard\Core\App::loadDefaultApp();
		// d(\Asgard\Core\App::get('resolver'));
	}
	
	public function test1() {
		$browser = new \Asgard\Utils\Browser;
		$doc = new \Asgard\Xpath\Doc($browser->get('')->content);
		$this->assertEquals($doc->text('//h1'), 'Asgard');
	}
}