<?php
namespace Asgard\Orm\Tests;

class I18NTest extends \PHPUnit_Framework_TestCase {
	protected static $app;

	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');

		$app = new \Asgard\Core\App;
		$app['hook'] = new \Asgard\Hook\Hook($app);
		$app['config'] = new \Asgard\Core\Config;
		$app['config']->set('locale', 'en');
		$app['config']->set('locales', array('en', 'fr'));
		$app['cache'] = new \Asgard\Cache\NullCache;
		$app['translator'] = new \Asgard\Translation\Translator('en');
		$app['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($app);
		$app['db'] = new \Asgard\Db\DB(array(
			'database' => 'asgard',
			'user' => 'root',
			'password' => '',
			'host' => 'localhost'
		));
		\Asgard\Entity\Entity::setApp($app);
		static::$app = $app;

		$app['db']->import(realpath(__DIR__.'/sql/i18ntest.sql'));
	}

	#get default
	public function test1() {
		$com = \Asgard\Orm\Tests\I18nentities\Commentaire::load(2);
		$actu = $com->actualite;
		$this->assertEquals('Hello', $actu->test); #default language is english
	}
    
	#save french text
	public function test2() {
		$com = \Asgard\Orm\Tests\I18nentities\Commentaire::load(2);
		$actu = $com->actualite;
		$this->assertEquals('Bonjour', $actu->get('test', 'fr'));
	}
    
	#get english text
	public function test3() {
		$com = \Asgard\Orm\Tests\I18nentities\Commentaire::load(2);
		$actu = $com->actualite;
		$this->assertEquals('Hello', $actu->get('test', 'en'));
	}
    
	#get all
	public function test4() {
		$com = \Asgard\Orm\Tests\I18nentities\Commentaire::load(2);
		$actu = $com->actualite;
		$this->assertContains('Bonjour', $actu->get('test', 'all'));
		$this->assertContains('Hello', $actu->get('test', 'all'));
		$this->assertCount(2, $actu->get('test', 'all'));
	}
    
	#save english version
	public function test5() {
		static::$app['translator']->setLocale('en');
		$actu = \Asgard\Orm\Tests\I18nentities\Actualite::load(2);
		$actu->test = 'Hi';
		$actu->save(null, true);
		$dal = new \Asgard\Db\DAL(static::$app['db'], static::$app['config']->get('database/prefix').'actualite_translation');
		$r = $dal->where(array('locale'=>'en', 'id'=>2))->first();
		$this->assertEquals('Hi', $r['test']);
	}
}
?>