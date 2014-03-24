<?php
namespace Asgard\Orm\Tests;

class I18NTest extends \PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
		require_once(_CORE_DIR_.'core.php');
		\Asgard\Core\App::instance(true)->config->set('bundles', array(
			_ASGARD_DIR_.'core',
			// _ASGARD_DIR_.'files',
			_ASGARD_DIR_.'orm',
		));
		\Asgard\Core\App::loadDefaultApp();

		\Asgard\Core\App::get('db')->import(realpath(dirname(__FILE__).'/sql/i18ntest.sql'));
	}

	#get default
	public function test1() {
		$com = new \Asgard\Orm\tests\i18Nentities\Commentaire(2);
		$actu = $com->actualite;
		$this->assertEquals('Hello', $actu->test); #default language is english
	}
    
	#save french text
	public function test2() {
		$com = new \Asgard\Orm\tests\i18Nentities\Commentaire(2);
		$actu = $com->actualite;
		$this->assertEquals('Bonjour', $actu->get('test', 'fr'));
	}
    
	#get english text
	public function test3() {
		$com = new \Asgard\Orm\tests\i18Nentities\Commentaire(2);
		$actu = $com->actualite;
		$this->assertEquals('Hello', $actu->get('test', 'en'));
	}
    
	#get all
	public function test4() {
		$com = new \Asgard\Orm\tests\i18Nentities\Commentaire(2);
		$actu = $com->actualite;
		$this->assertContains('Bonjour', $actu->get('test', 'all'));
		$this->assertContains('Hello', $actu->get('test', 'all'));
		$this->assertCount(2, $actu->get('test', 'all'));
	}
    
	#save english version
	public function test5() {
		\Asgard\Core\App::get('locale')->setLocale('en');
		$actu = new \Asgard\Orm\Tests\I18Nentities\Actualite(2);
		$actu->test = 'Hi';
		// d($actu->data['properties']);
		$actu->save(null, true);
		$dal = new \Asgard\Db\DAL(\Asgard\Core\App::get('db'), \Asgard\Core\App::get('config')->get('database/prefix').'actualite_translation');
		$r = $dal->where(array('locale'=>'en', 'id'=>2))->first();
		$this->assertEquals('Hi', $r['test']);
	}
}
?>