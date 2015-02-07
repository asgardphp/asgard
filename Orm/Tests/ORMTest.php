<?php
namespace Asgard\Orm\Tests;

class ORMTest extends \PHPUnit_Framework_TestCase {
	public function testHasOne() {
		#Deps
		$db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => 'test.db',
		]);
		$em = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db, $em);
		$schema = $db->getSchema();
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Fixtures\ORM\A'),
			$em->get('Asgard\Orm\Tests\Fixtures\ORM\B'),
		]);

		#Fixtures
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\ORM\A', [
			'id' => 1,
			'name' => 'foo',
			'b' =>  $dataMapper->create('Asgard\Orm\Tests\Fixtures\ORM\B', [
				'id' => 1,
				'name' => 'bar',
			])
		]);

		$a = $dataMapper->load('Asgard\Orm\Tests\Fixtures\ORM\A', 1);

		$this->assertInstanceOf('Asgard\Orm\Tests\Fixtures\ORM\B', $dataMapper->getRelated($a, 'b'));
	}

	public function testHMABTSorting() {
		#Deps
		$db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]);
		$em = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db, $em);

		#DB
		$schema = $db->getSchema();
		$schema->drop('news');
		$schema->drop('tag');
		$schema->drop('news_tag');
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Fixtures\HMABTSorting\News'),
			$em->get('Asgard\Orm\Tests\Fixtures\HMABTSorting\Tag'),
		]);

		#Fixtures
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\HMABTSorting\News', [
			'id' => 1,
			'title' => 'Hello!',
			'tags'  => [
				$dataMapper->create('Asgard\Orm\Tests\Fixtures\HMABTSorting\Tag', [
					'id' => 3,
					'name' => 'Economy',
				]),
				$dataMapper->create('Asgard\Orm\Tests\Fixtures\HMABTSorting\Tag', [
					'id' => 1,
					'name' => 'General',
				]),
				$dataMapper->create('Asgard\Orm\Tests\Fixtures\HMABTSorting\Tag', [
					'id' => 2,
					'name' => 'Science',
				]),
			]
		]);

		$news = $dataMapper->load('Asgard\Orm\Tests\Fixtures\HMABTSorting\News', 1);
		$tagsIDs = $dataMapper->related($news, 'tags')->ids();
		$this->assertEquals(
			[3, 1, 2],
			$tagsIDs
		);

		#Eager loading
		$news = $dataMapper->orm('Asgard\Orm\Tests\Fixtures\HMABTSorting\News')->where('id', 1)->with('tags')->first();
		$tagsIDs = [];
		foreach($news->tags as $tag)
			$tagsIDs[] = $tag->id;
		$this->assertEquals(
			[3, 1, 2],
			$tagsIDs
		);
	}

	public function testUpdateAliasInWhere() {
		#Dependencies
		$em = new \Asgard\Entity\EntityManager;
		$rulesRegistry = new \Asgard\Validation\RulesRegistry;
		$rulesRegistry->registerNamespace('Asgard\Orm\Rules');
		$em->setValidatorFactory(new \Asgard\Validation\ValidatorFactory($rulesRegistry));

		$db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]);
		$dataMapper = new \Asgard\Orm\DataMapper($db, $em);
	}

	public function test1() {
		#Dependencies
		$em = new \Asgard\Entity\EntityManager;
		$rulesRegistry = new \Asgard\Validation\RulesRegistry;
		$rulesRegistry->registerNamespace('Asgard\Orm\Rules');
		$em->setValidatorFactory(new \Asgard\Validation\ValidatorFactory($rulesRegistry));

		$db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]);
		$dataMapper = new \Asgard\Orm\DataMapper($db, $em);

		#Create tables
		$schema = $db->getSchema();
		$schema->drop('category');
		$schema->drop('news');
		$schema->drop('author');
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Fixtures\ORM\Category'),
			$em->get('Asgard\Orm\Tests\Fixtures\ORM\News'),
			$em->get('Asgard\Orm\Tests\Fixtures\ORM\Author'),
		]);

		#Fixtures
		$author1 = $dataMapper->create('Asgard\Orm\Tests\Fixtures\ORM\Author', [
			'name' => 'Bob',
		]);
		$author2 = $dataMapper->create('Asgard\Orm\Tests\Fixtures\ORM\Author', [
			'name' => 'Joe',
		]);
		$author3 = $dataMapper->create('Asgard\Orm\Tests\Fixtures\ORM\Author', [
			'name' => 'John',
		]);
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\ORM\Category',
			[
				'title' => 'General',
				'description' => 'General new',
				'news' => [
					$dataMapper->create('Asgard\Orm\Tests\Fixtures\ORM\News', [
						'title' => 'Welcome!',
						'content' => 'blabla',
						'author' => $author1,
						'score' => '2',
					]),
					$dataMapper->create('Asgard\Orm\Tests\Fixtures\ORM\News', [
						'title' => '1000th visitor!',
						'content' => 'blabla',
						'author' => $author2,
						'score' => '5',
					])
				]
			],
			$force=true
		);
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\ORM\Category',
			[
				'title' => 'Misc',
				'description' => 'Other news',
				'news' => [
					$dataMapper->create('Asgard\Orm\Tests\Fixtures\ORM\News', [
						'title' => 'Important',
						'content' => 'blabla',
						'author' => $author1,
						'score' => '1',
					])
				]
			],
			$force=true
		);

		#load
		$cat = $dataMapper->load('Asgard\Orm\Tests\Fixtures\ORM\Category', 1);
		$this->assertEquals(1, $cat->id);
		$this->assertEquals('General', $cat->title);

		#relation count
		$this->assertEquals(2, $dataMapper->related($cat, 'news')->count());

		#orderBy
		$this->assertEquals(2, $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\Category')->first()->id); #default order is id DESC
		$this->assertEquals(2, $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\Category')->orderBy('id DESC')->first()->id);
		$this->assertEquals(1, $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\Category')->orderBy('id ASC')->first()->id);

		#relation shortcut
		$this->assertEquals(2, count($dataMapper->related($cat, 'news')->get()));

		#relation + where
		$this->assertEquals(1, $dataMapper->related($cat, 'news')->where('title', 'Welcome!')->first()->id);

		#joinToEntity
		$this->assertEquals(
			1,
			$dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->joinToEntity('category', $cat)->where('title', 'Welcome!')->first()->id
		);
		$author = $dataMapper->load('Asgard\Orm\Tests\Fixtures\ORM\Author', 2);
		$this->assertEquals(
			2,
			$dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->joinToEntity('category', $cat)->joinToEntity('author', $author)->where('author.name', 'Joe')->first()->id
		);
		$this->assertEquals(
			null,
			$dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->joinToEntity('category', $cat)->joinToEntity('author', $author)->where('author.name', 'Bob')->first()
		);

		#stats functions
		$this->assertEquals(26, floor($dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->avg('score')*10));
		$this->assertEquals(8, $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->sum('score'));
		$this->assertEquals(5, $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->max('score'));
		$this->assertEquals(1, $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->min('score'));

		#relations cascade
		$this->assertEquals(2, count($dataMapper->related($cat, 'news')->author));
		$this->assertEquals(1, $dataMapper->related($cat, 'news')->author()->where('name', 'Bob')->first()->id);

		#join
		$this->assertEquals(
			2,
			$dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\Author')
			->join('news')
			->where('news.title', '1000th visitor!')
			->first()
			->id
		);

		#next
		$news = [];
		$orm = $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News');
		while($n = $orm->next())
			$news[] = $n;
		$this->assertEquals(3, count($news));

		#values
		$this->assertEquals(
			['Welcome!', '1000th visitor!', 'Important'],
			$dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->orderBy('id ASC')->values('title')
		);

		#ids
		$this->assertEquals(
			[1, 2, 3],
			$dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->orderBy('id ASC')->ids()
		);

		#with
		$cats = $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\Category')->with('news')->get();
		$this->assertEquals(1, count($cats[0]->data['properties']['news']));
		$this->assertEquals(2, count($cats[1]->data['properties']['news']));

		$cats = $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\Category')->with('news', function($orm) {
			$orm->with('author');
		})->get();
		$this->assertEquals(1, $cats[0]->data['properties']['news'][0]->data['properties']['author']->id);

		#selectQuery
		$cats = $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\Category')->selectQuery('SELECT * FROM category WHERE title=?', ['General']);
		$this->assertEquals(1, $cats[0]->id);

		#paginate
		$orm = $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->paginate(1, 2);
		$paginator = $orm->getPaginator();
		$this->assertTrue($paginator instanceof \Asgard\Common\Paginator);
		$this->assertEquals(2, count($orm->get()));
		$this->assertEquals(1, count($dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->paginate(2, 2)->get()));

		#offset
		$this->assertEquals(3, $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->orderBy('id ASC')->offset(2)->first()->id);

		#limit
		$this->assertCount(2, $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')->limit(2)->get());

		#two jointures with the same name
		$r = $dataMapper->orm('Asgard\Orm\Tests\Fixtures\ORM\News')
		->join([
			'author' => 'news n1',
			'category' => 'news n2',
		])
		->where('n2.title', 'Welcome!')
		->get();
		$this->assertCount(2, $r);

		#validation
		$cat = $dataMapper->load('Asgard\Orm\Tests\Fixtures\ORM\Category', 1);
		$this->assertEquals([
			'news' => [
				'ormhasmorethan' => 'News must have more than 3 elements.'
			]
		], $dataMapper->relationsErrors($cat));
		$cat = $em->make('Asgard\Orm\Tests\Fixtures\ORM\Category');
		$this->assertEquals([
			'news' => [
				'ormrequired' => 'News is required.',
				'ormhasmorethan' => 'News must have more than 3 elements.'
			]
		], $dataMapper->relationsErrors($cat));

		#

		#test polymorphic
			// hmabt/hasmany?
		#test i18n
		#probleme quand on set limit, offset, etc. dans l'orm pour enchainer?
		/*
		#all()
		delete()
		update
		reset()
		behavior
			new entity
			getTable
			validation des relations
			orm
			load
			destroyAll
			destroyOne
			hasRelation
			loadBy
			isNew
			isOld
			relation
			getRelationProperty
			destroy
			save
			get i18n
		ORMManager
			loadEntityFixtures
			diff
			migrate
			current
			uptodate
			runMigration
			todo
			automigrate
		*/

	}

	#all together
	public function test() {
		return;
/*
		#get all the authors in page 1 (10 per page), which belong to news that have score > 3 and belongs to category "general", and with their comments, and all in english only.
		$authors = Asgard\Orm\Tests\Fixtures\ORM\Categoryi18n::loadByName('general') #from the category "general"
		->news() #get the news
		->where('score > 3') #whose score is greater than 3
		->author() #the authors from the previous news
		->with([ #along with their comments
			'comments'
		])
		->paginate(1, 10) #paginate, 10 authors per page, page 1
		->get();*/
	}
}