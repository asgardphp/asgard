<?php
namespace Asgard\Orm\Tests;

class I18NTest extends \PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
		\Asgard\Core\App::instance(true)->config->set('bundles', array(
			new \Asgard\Orm\Bundle,
			new \Asgard\Validation\Bundle,
			new \Asgard\Entity\Bundle,
		))->set('bundlesdirs', array());
		\Asgard\Core\App::loadDefaultApp(false);

		\Asgard\Core\App::get('db')->import(realpath(__DIR__.'/sql/i18ntest.sql'));
	}

	#get default
	public function test1() {
		$com = \Asgard\Orm\tests\i18Nentities\Commentaire::load(2);
		$actu = $com->actualite;
		$this->assertEquals('Hello', $actu->test); #default language is english
	}
    
	#save french text
	public function test2() {
		$com = \Asgard\Orm\tests\i18Nentities\Commentaire::load(2);
		$actu = $com->actualite;
		$this->assertEquals('Bonjour', $actu->get('test', 'fr'));
	}
    
	#get english text
	public function test3() {
		$com = \Asgard\Orm\tests\i18Nentities\Commentaire::load(2);
		$actu = $com->actualite;
		$this->assertEquals('Hello', $actu->get('test', 'en'));
	}
    
	#get all
	public function test4() {
		$com = \Asgard\Orm\tests\i18Nentities\Commentaire::load(2);
		$actu = $com->actualite;
		$this->assertContains('Bonjour', $actu->get('test', 'all'));
		$this->assertContains('Hello', $actu->get('test', 'all'));
		$this->assertCount(2, $actu->get('test', 'all'));
	}
    
	#save english version
	public function test5() {
		\Asgard\Core\App::get('translator')->setLocale('en');
		$actu = \Asgard\Orm\tests\i18Nentities\Actualite::load(2);
		$actu->test = 'Hi';
		$actu->save(null, true);
		$dal = new \Asgard\Db\DAL(\Asgard\Core\App::get('db'), \Asgard\Core\App::get('config')->get('database/prefix').'actualite_translation');
		$r = $dal->where(array('locale'=>'en', 'id'=>2))->first();
		$this->assertEquals('Hi', $r['test']);
	}
}
?>