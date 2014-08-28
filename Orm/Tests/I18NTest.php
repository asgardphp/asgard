<?php
namespace Asgard\Orm\Tests;

class I18NTest extends \PHPUnit_Framework_TestCase {
	protected static $container;

	public static function setUpBeforeClass() {
		$container = new \Asgard\Container\Container;
		$container['hooks']             = new \Asgard\Hook\HooksManager($container);
		$container['cache']             = new \Asgard\Cache\NullCache;
		$config = [
			'database' => 'asgard',
			'user'     => 'root',
			'password' => '',
			'host'     => 'localhost'
		];
		$container['db'] = new \Asgard\Db\DB($config);
		$container->register('datamapper', function($container) {
			return new \Asgard\Orm\DataMapper(
				$container['db'],
				'en',
				'',
				$container
			);
		});

		$entitiesManager = $container['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($container);
		#set the EntitiesManager static instance for activerecord-like entities (e.g. new Article or Article::find())
		\Asgard\Entity\EntitiesManager::setInstance($entitiesManager);
		$entitiesManager->setDefaultLocale('en');

		static::$container = $container;

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
		$news = \Asgard\Orm\Tests\I18nentities\News::load(2);
		$news->test = 'Hi';
		$news->save(null, true);
		$dal = new \Asgard\Db\DAL(static::$container['db'], 'news_translation');
		$r = $dal->where(['locale'=>'en', 'id'=>2])->first();
		$this->assertEquals('Hi', $r['test']);
	}
}
