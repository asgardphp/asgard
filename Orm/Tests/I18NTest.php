<?php
namespace Asgard\Orm\Tests;

class I18NTest extends \PHPUnit_Framework_TestCase {
	protected static $dm;
	protected static $db;

	public static function setUpBeforeClass() {
		$config = [
			'driver' => 'sqlite',
			'database' => ':memory:',
		];
		static::$db = $db = new \Asgard\Db\DB($config);

		$entityManager = new \Asgard\Entity\EntityManager;
		$entityManager->setDefaultLocale('en');
		static::$dm = new \Asgard\Orm\DataMapper(
			$db,
			$entityManager
		);

		$schema = $db->getSchema();

		#Comment
		$schema->create('comment', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 4,
				'notnull' => true,
				'autoincrement' => true,
			]);
			$table->addColumn('title', 'text', [
				'notnull' => true,
			]);
			$table->addColumn('created_at', 'datetime', [
				'notnull' => true,
			]);
			$table->addColumn('updated_at', 'datetime', [
				'notnull' => true,
			]);
			$table->addColumn('news_id', 'integer', [
				'length' => 11,
				'notnull' => true,
			]);

			$table->setPrimaryKey(['id']);
		});
		$db->dal()->into('comment')->insert(['id'=>2, 'title'=>'comment', 'created_at'=>'2012-01-00 00:00:00', 'updated_at'=>'2012-07-09 20:24:09', 'news_id'=>2]);

		#news_translation
		$schema->create('news_translation', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 4,
				'notnull' => true,
				'autoincrement' => true,
			]);
			$table->addColumn('locale', 'string', [
				'length' => 10,
				'notnull' => true,
			]);
			$table->addColumn('test', 'text', [
				'notnull' => true,
			]);
		});
		$db->dal()->into('news_translation')->insert(['id'=>2, 'locale'=>'en', 'test'=>'Hello']);
		$db->dal()->into('news_translation')->insert(['id'=>2, 'locale'=>'fr', 'test'=>'Bonjour']);

		#news
		$schema->create('news', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 4,
				'notnull' => true,
				'autoincrement' => true,
			]);
			$table->addColumn('title', 'text', [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['id']);
		});
		$db->dal()->into('news')->insert(['id'=>2, 'title'=>'news!']);

		#news_comment
		$schema->create('news_comment', function($table) {
			$table->addColumn('news_id', 'integer', [
				'length' => 11,
				'notnull' => true,
			]);
			$table->addColumn('comment_id', 'integer', [
				'length' => 11,
				'notnull' => true,
			]);
		});
		$db->dal()->into('news_comment')->insert(['news_id'=>2, 'comment_id'=>2]);
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
