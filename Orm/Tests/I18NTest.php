<?php
namespace Asgard\Orm\Tests;

class I18NTest extends \PHPUnit_Framework_TestCase {
	protected static $dm;
	protected static $db;

	public static function setUpBeforeClass() {
		$config = [
			'database' => 'asgard',
			'user'     => 'root',
			'password' => '',
			'host'     => 'localhost'
		];
		static::$db = $db = new \Asgard\Db\DB($config);

		$entityManager = new \Asgard\Entity\EntityManager;
		$entityManager->setDefaultLocale('en');
		static::$dm = new \Asgard\Orm\DataMapper(
			$db,
			$entityManager
		);

		(new \Asgard\Db\MySQL($config))->import(realpath(__DIR__.'/sql/i18ntest.sql'));
	}

	#get default
	public function test1() {
		$com = static::$dm->load('Asgard\Orm\Tests\Fixtures\I18n\Comment', 2);
		$news = static::$dm->getRelated($com, 'news');
		$this->assertEquals('Hello', $news->test); #default language is english
	}

	#save french text
	public function test2() {
		$com = static::$dm->load('Asgard\Orm\Tests\Fixtures\I18n\Comment', 2);
		$news = static::$dm->getRelated($com, 'news');
		static::$dm->getTranslations($news, 'fr');
		$this->assertEquals('Bonjour', $news->get('test', 'fr'));
	}

	#get english text
	public function test3() {
		$com = static::$dm->load('Asgard\Orm\Tests\Fixtures\I18n\Comment', 2);
		$news = static::$dm->getRelated($com, 'news');
		static::$dm->getTranslations($news, 'en');
		$this->assertEquals('Hello', $news->get('test', 'en'));
	}

	#get all
	public function test4() {
		$com = static::$dm->load('Asgard\Orm\Tests\Fixtures\I18n\Comment', 2);
		$news = static::$dm->getRelated($com, 'news');
		static::$dm->getTranslations($news, 'en');
		static::$dm->getTranslations($news, 'fr');
		$this->assertContains('Bonjour', $news->get('test', ['en', 'fr']));
		$this->assertContains('Hello', $news->get('test', ['en', 'fr']));
		$this->assertCount(2, $news->get('test', ['en', 'fr']));
	}

	#save english version
	public function test5() {
		$news = static::$dm->load('Asgard\Orm\Tests\Fixtures\I18n\News', 2);
		$news->test = 'Hi';
		static::$dm->save($news, null, true);
		$dal = new \Asgard\Db\DAL(static::$db, 'news_translation');
		$r = $dal->where(['locale'=>'en', 'id'=>2])->first();
		$this->assertEquals('Hi', $r['test']);
	}
}
