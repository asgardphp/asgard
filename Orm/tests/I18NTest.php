<?php
namespace Asgard\Orm\Tests;

class I18NTest extends \PHPUnit_Framework_TestCase {
	protected static $app;

	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');

		$app = new \Asgard\Container\Container();
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$app['kernel'] = new \Asgard\Core\Kernel();
		$app['config'] = new \Asgard\Config\Config();
		$app['config']->set('locale', 'en');
		$app['config']->set('locales', ['en', 'fr']);
		$app['cache'] = new \Asgard\Cache\NullCache;
		$app['translator'] = new \Symfony\Component\Translation\Translator('en');
		$app['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($app);
		$config = [
			'database' => 'asgard',
			'user' => 'root',
			'password' => '',
			'host' => 'localhost'
		];
		$app['db'] = new \Asgard\Db\DB($config);
		\Asgard\Entity\Entity::setApp($app);
		static::$app = $app;

		$mysql = new \Asgard\Db\MySQL($config);
		$mysql->import(realpath(__DIR__.'/sql/i18ntest.sql'));
	}

	#get default
	public function test1() {
		$com = \Asgard\Orm\Tests\I18nentities\Comment::load(2);
		$news = $com->news;
		$this->assertEquals('Hello', $news->test); #default language is english
	}
    
	#save french text
	public function test2() {
		$com = \Asgard\Orm\Tests\I18nentities\Comment::load(2);
		$news = $com->news;
		$this->assertEquals('Bonjour', $news->get('test', 'fr'));
	}
    
	#get english text
	public function test3() {
		$com = \Asgard\Orm\Tests\I18nentities\Comment::load(2);
		$news = $com->news;
		$this->assertEquals('Hello', $news->get('test', 'en'));
	}
    
	#get all
	public function test4() {
		$com = \Asgard\Orm\Tests\I18nentities\Comment::load(2);
		$news = $com->news;
		$this->assertContains('Bonjour', $news->get('test', ['en', 'fr']));
		$this->assertContains('Hello', $news->get('test', ['en', 'fr']));
		$this->assertCount(2, $news->get('test', ['en', 'fr']));
	}
    
	#save english version
	public function test5() {
		static::$app['translator']->setLocale('en');
		$news = \Asgard\Orm\Tests\I18nentities\News::load(2);
		$news->test = 'Hi';
		$news->save(null, true);
		$dal = new \Asgard\Db\DAL(static::$app['db'], 'news_translation');
		$r = $dal->where(['locale'=>'en', 'id'=>2])->first();
		$this->assertEquals('Hi', $r['test']);
	}
}
