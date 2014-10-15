<?php
namespace Asgard\Orm\Tests;

class PolymorphismTest extends \PHPUnit_Framework_TestCase {
	protected static $em;
	protected static $ormm;
	protected static $dm;
	protected static $schema;

	public static function setUpBeforeClass() {
		$db = new \Asgard\Db\DB([
			'host' => 'localhost',
			'user' => 'root',
			'password' => '',
			'database' => 'asgard_test'
		]);
		static::$em = new \Asgard\Entity\EntityManager;
		static::$dm = new \Asgard\Orm\DataMapper($db);
		static::$ormm  = new \Asgard\Orm\ORMMigrations(static::$dm);
		static::$schema = new \Asgard\Db\Schema($db);
	}

	public function testHasManyBelongsTo() {
		$dm = static::$dm;
		static::$schema->dropAll();
		static::$ormm->autoMigrate([
			static::$em->get('Asgard\Orm\Tests\Fixtures\Polymorphism\Article'),
			static::$em->get('Asgard\Orm\Tests\Fixtures\Polymorphism\Tag'),
		], static::$schema);

		static::$schema->emptyAll();

		$tag = $dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Tag', [
			'name' => 'foo',
			'article' => $dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Article', [
				'title' => 'bar'
			])
		]);

		$this->assertEquals('bar', $tag->article->title);
		$tag = $dm->load('Asgard\Orm\Tests\Fixtures\Polymorphism\Tag', 1);
		$this->assertEquals('foo', $tag->name);
		$this->assertEquals('bar', $dm->getRelated($tag, 'article')->title);

		static::$schema->emptyAll();

		$article = $dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Article', [
			'title' => 'bar',
			'tags' => [
				$dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Tag', [
					'name' => 'foo',
				])
			]
		]);

		$this->assertEquals('foo', $article->tags[0]->name);
		$article = $dm->load('Asgard\Orm\Tests\Fixtures\Polymorphism\Article', 1);
		$this->assertEquals('bar', $article->title);
		$this->assertEquals('foo', $dm->getRelated($article, 'tags')[0]->name);
	}

	public function testBelongsToHasMany() {
		$dm = static::$dm;
		static::$schema->dropAll();
		static::$ormm->autoMigrate([
			static::$em->get('Asgard\Orm\Tests\Fixtures\Polymorphism\User'),
			static::$em->get('Asgard\Orm\Tests\Fixtures\Polymorphism\Document'),
		], static::$schema);

		static::$schema->emptyAll();

		$document = $dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Document', [
			'title' => 'foo',
			'user' => $dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\User', [
				'name' => 'bar'
			])
		]);

		$this->assertEquals('bar', $document->user->name);
		$document = $dm->load('Asgard\Orm\Tests\Fixtures\Polymorphism\Document', 1);
		$this->assertEquals('foo', $document->title);
		$this->assertEquals('bar', $dm->getRelated($document, 'user')->name);

		static::$schema->emptyAll();

		$user = $dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\User', [
			'name' => 'bar',
			'documents' => [
				$dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Document', [
					'title' => 'foo',
				])
			]
		]);

		$this->assertEquals('foo', $user->documents[0]->title);
		$user = $dm->load('Asgard\Orm\Tests\Fixtures\Polymorphism\User', 1);
		$this->assertEquals('bar', $user->name);
		$this->assertEquals('foo', $dm->getRelated($user, 'documents')[0]->title);

	}

	public function testHasOne() {
		$dm = static::$dm;
		static::$schema->dropAll();
		static::$ormm->autoMigrate([
			static::$em->get('Asgard\Orm\Tests\Fixtures\Polymorphism\Article2'),
			static::$em->get('Asgard\Orm\Tests\Fixtures\Polymorphism\Author'),
		], static::$schema);

		static::$schema->emptyAll();

		$article = $dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Article2', [
			'title' => 'bar',
			'author' => $dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Author', [
				'name' => 'foo',
			])
		]);

		$this->assertEquals('foo', $article->author->name);
		$article = $dm->load('Asgard\Orm\Tests\Fixtures\Polymorphism\Article2', 1);
		$this->assertEquals('bar', $article->title);
		$this->assertEquals('foo', $dm->getRelated($article, 'author')->name);

		static::$schema->emptyAll();

		$author = $dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Author', [
			'name' => 'foo',
			'article' => $dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Article2', [
				'title' => 'bar',
			])
		]);

		$this->assertEquals('bar', $author->article->title);
		$author = $dm->load('Asgard\Orm\Tests\Fixtures\Polymorphism\Author', 1);
		$this->assertEquals('foo', $author->name);
		$this->assertEquals('bar', $dm->getRelated($author, 'article')->title);

	}

	public function testHMABT() {
		$dm = static::$dm;
		static::$schema->dropAll();
		static::$ormm->autoMigrate([
			static::$em->get('Asgard\Orm\Tests\Fixtures\Polymorphism\Article3'),
			static::$em->get('Asgard\Orm\Tests\Fixtures\Polymorphism\Category'),
		], static::$schema);

		static::$schema->emptyAll();

		$article = $dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Article3', [
			'title' => 'bar',
			'categories' => [
				$dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Category', [
					'name' => 'foo',
				])
			]
		]);

		$this->assertEquals('foo', $article->categories[0]->name);
		$article = $dm->load('Asgard\Orm\Tests\Fixtures\Polymorphism\Article3', 1);
		$this->assertEquals('bar', $article->title);
		$this->assertEquals('foo', $dm->getRelated($article, 'categories')[0]->name);

		static::$schema->emptyAll();

		$category = $dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Category', [
			'name' => 'foo',
			'articles' => [
				$dm->create('Asgard\Orm\Tests\Fixtures\Polymorphism\Article3', [
					'title' => 'bar',
				])
			]
		]);

		$this->assertEquals('bar', $category->articles[0]->title);
		$category = $dm->load('Asgard\Orm\Tests\Fixtures\Polymorphism\Category', 1);
		$this->assertEquals('foo', $category->name);
		$this->assertEquals('bar', $dm->getRelated($category, 'articles')[0]->title);
	}
}