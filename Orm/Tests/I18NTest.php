<?php
namespace Asgard\Orm\Tests;

class I18NTest extends \PHPUnit_Framework_TestCase {
	protected static $container;

	public static function setUpBeforeClass() {
		$container          = new \Asgard\Container\Container;
		$container['hooks'] = new \Asgard\Hook\HookManager($container);
		$config = [
			'database' => 'asgard',
			'user'     => 'root',
			'password' => '',
			'host'     => 'localhost'
		];
		$container['db'] = new \Asgard\Db\DB($config);

		$entityManager = $container['entityManager'] = new \Asgard\Entity\EntityManager($container);
		$entityManager->setDefaultLocale('en');

		$container->register('datamapper', function($container) {
			return new \Asgard\Orm\DataMapper(
				$container['db'],
				$container['entityManager']
			);
		});

		static::$container = $container;

		$mysql = new \Asgard\Db\MySQL($config);
		$mysql->import(realpath(__DIR__.'/sql/i18ntest.sql'));
	}

	#get default
	public function test1() {
		$com = static::$container['dataMapper']->load('Asgard\Orm\Tests\I18nentities\Comment', 2);
		$news = static::$container['dataMapper']->getRelated($com, 'news');
		$this->assertEquals('Hello', $news->test); #default language is english
	}

	#save french text
	public function test2() {
		$com = static::$container['dataMapper']->load('Asgard\Orm\Tests\I18nentities\Comment', 2);
		$news = static::$container['dataMapper']->getRelated($com, 'news');
		static::$container['dataMapper']->getTranslations($news, 'fr');
		$this->assertEquals('Bonjour', $news->get('test', 'fr'));
	}

	#get english text
	public function test3() {
		$com = static::$container['dataMapper']->load('Asgard\Orm\Tests\I18nentities\Comment', 2);
		$news = static::$container['dataMapper']->getRelated($com, 'news');
		static::$container['dataMapper']->getTranslations($news, 'en');
		$this->assertEquals('Hello', $news->get('test', 'en'));
	}

	#get all
	public function test4() {
		$com = static::$container['dataMapper']->load('Asgard\Orm\Tests\I18nentities\Comment', 2);
		$news = static::$container['dataMapper']->getRelated($com, 'news');
		static::$container['dataMapper']->getTranslations($news, 'en');
		static::$container['dataMapper']->getTranslations($news, 'fr');
		$this->assertContains('Bonjour', $news->get('test', ['en', 'fr']));
		$this->assertContains('Hello', $news->get('test', ['en', 'fr']));
		$this->assertCount(2, $news->get('test', ['en', 'fr']));
	}

	#save english version
	public function test5() {
		$news = static::$container['dataMapper']->load('Asgard\Orm\Tests\I18nentities\News', 2);
		$news->test = 'Hi';
		static::$container['dataMapper']->save($news, null, true);
		$dal = new \Asgard\Db\DAL(static::$container['db'], 'news_translation');
		$r = $dal->where(['locale'=>'en', 'id'=>2])->first();
		$this->assertEquals('Hi', $r['test']);
	}
}
