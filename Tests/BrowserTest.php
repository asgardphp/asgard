<?php
namespace Asgard\Utils\Tests;

class BrowserTest extends \PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
		\Asgard\Core\App::instance(true)->config
		->set('bundles', array(
			'app/general',
			new \Asgard\Http\Bundle
		))
		->set('bundlesdirs', array(
			// 'app'
		));
		\Asgard\Core\App::loadDefaultApp(false);
	}
	
	public function test1() {
		$browser = new \Asgard\Utils\Browser;
		$doc = new \Asgard\Xpath\Doc($browser->get('')->content);
		$this->assertEquals($doc->text('//h1'), 'Asgard');
	}
}