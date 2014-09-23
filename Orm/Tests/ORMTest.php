<?php
namespace Asgard\Orm\Tests;

class ORMTest extends \PHPUnit_Framework_TestCase {
	protected static $container;

	public static function setUpBeforeClass() {
		$container                  = new \Asgard\Container\Container;
		$container['hooks']         = new \Asgard\Hook\HooksManager($container);
		$container['config']        = new \Asgard\Config\Config;
		$container['cache']         = new \Asgard\Cache\NullCache;
		$container['rulesregistry'] = new \Asgard\Validation\RulesRegistry;
		$container->register('validator', function($container) {
			$validator = new \Asgard\Validation\Validator;
			$validator->setRegistry($container['rulesregistry']);
			return $validator;
		});
		$container['db'] = new \Asgard\Db\DB([
			'database' => 'asgard',
			'user'     => 'root',
			'password' => '',
			'host'     => 'localhost'
		]);
		$container->register('paginator', function($container, $count, $page, $per_page) {
			return new \Asgard\Common\Paginator($count, $page, $per_page);
		});
		$container->register('orm', function($container, $entityClass, $locale, $prefix, $dataMapper) {
			return new \Asgard\Orm\ORM($entityClass, $locale, $prefix, $dataMapper, $container->createFactory('paginator'));
		});
		$container->register('collectionOrm', function($container, $entityClass, $name, $locale, $prefix, $dataMapper) {
			return new \Asgard\Orm\CollectionORM($entityClass, $name, $locale, $prefix, $dataMapper, $container->createFactory('paginator'));
		});

		$entitiesManager = $container['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($container);
		$entitiesManager->setValidatorFactory($container->createFactory('validator'));
		#set the EntitiesManager static instance for activerecord-like entities (e.g. new Article or Article::find())
		\Asgard\Entity\EntitiesManager::setInstance($entitiesManager);
		
		$container->register('datamapper', function($container) {
			return new \Asgard\Orm\DataMapper(
				$container['entitiesManager'],
				$container['db'],
				'en',
				'',
				$container->createFactory('orm'),
				$container->createFactory('collectionOrm')
			);
		});


		static::$container = $container;
	}
	
	public function test1() {
		#Dependencies
		$container = new \Asgard\Container\Container;
		$container['rulesRegistry'] = new \Asgard\Validation\RulesRegistry;
		$container['rulesRegistry']->registerNamespace('Asgard\Orm\Rules');
		#todo should i use full classnames?
		$em = new \Asgard\Entity\EntitiesManager;
		$em->setValidatorFactory($container->createFactory(function($container) {
			$validator = new \Asgard\Validation\Validator;
			return $validator->setRegistry($container['rulesregistry']);
		}));
		$db = new \Asgard\Db\DB([
			'host'     => 'localhost',
			'user'     => 'root',
			'password' => '',
			'database' => 'asgard'
		]);
		$dataMapper = new \Asgard\Orm\DataMapper($em, $db);

		#Create tables
		$schema = new \Asgard\Db\Schema($db);
		$schema->drop('category');
		$schema->drop('news');
		$schema->drop('author');
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Entities\Category'),
			$em->get('Asgard\Orm\Tests\Entities\News'),
			$em->get('Asgard\Orm\Tests\Entities\Author'),
		], $schema);

		#Fixtures
		$author1 = $dataMapper->create('Asgard\Orm\Tests\Entities\Author', [
			'name' => 'Bob',
		]);
		$author2 = $dataMapper->create('Asgard\Orm\Tests\Entities\Author', [
			'name' => 'Joe',
		]);
		$author3 = $dataMapper->create('Asgard\Orm\Tests\Entities\Author', [
			'name' => 'John',
		]);
		$dataMapper->create('Asgard\Orm\Tests\Entities\Category',
			[
				'title' => 'General',
				'description' => 'General new',
				'news' => [
					$dataMapper->create('Asgard\Orm\Tests\Entities\News', [
						'title' => 'Welcome!',
						'content' => 'blabla',
						'author' => $author1,
						'score' => '2',
					]),
					$dataMapper->create('Asgard\Orm\Tests\Entities\News', [
						'title' => '1000th visitor!',
						'content' => 'blabla',
						'author' => $author2,
						'score' => '5',
					])
				]
			],
			$force=true
		);
		$dataMapper->create('Asgard\Orm\Tests\Entities\Category',
			[
				'title' => 'Misc',
				'description' => 'Other news',
				'news' => [
					$dataMapper->create('Asgard\Orm\Tests\Entities\News', [
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
		$cat = $dataMapper->load('Asgard\Orm\Tests\Entities\Category', 1);
		$this->assertEquals(1, $cat->id);
		$this->assertEquals('General', $cat->title);

		#relation count
		$this->assertEquals(2, $dataMapper->related($cat, 'news')->count());

		#orderBy
		$this->assertEquals(2, $dataMapper->orm('Asgard\Orm\Tests\Entities\Category')->first()->id); #default order is id DESC
		$this->assertEquals(2, $dataMapper->orm('Asgard\Orm\Tests\Entities\Category')->orderBy('id DESC')->first()->id);
		$this->assertEquals(1, $dataMapper->orm('Asgard\Orm\Tests\Entities\Category')->orderBy('id ASC')->first()->id);

		#relation shortcut
		$this->assertEquals(2, count($dataMapper->related($cat, 'news')->get()));

		#relation + where
		$this->assertEquals(1, $dataMapper->related($cat, 'news')->where('title', 'Welcome!')->first()->id);

		#joinToEntity
		$this->assertEquals(
			1, 
			$dataMapper->orm('Asgard\Orm\Tests\Entities\News')->joinToEntity('category', $cat)->where('title', 'Welcome!')->first()->id
		);
		$author = $dataMapper->load('Asgard\Orm\Tests\Entities\Category', 2);
		$this->assertEquals(
			2, 
			$dataMapper->orm('Asgard\Orm\Tests\Entities\News')->joinToEntity('category', $cat)->joinToEntity('author', $author)->where('author.name', 'Joe')->first()->id
		);
		$this->assertEquals(
			null,
			$dataMapper->orm('Asgard\Orm\Tests\Entities\News')->joinToEntity('category', $cat)->joinToEntity('author', $author)->where('author.name', 'Bob')->first()
		);

		#stats functions
		$this->assertEquals(26, floor($dataMapper->orm('Asgard\Orm\Tests\Entities\News')->avg('score')*10));
		$this->assertEquals(8, $dataMapper->orm('Asgard\Orm\Tests\Entities\News')->sum('score'));
		$this->assertEquals(5, $dataMapper->orm('Asgard\Orm\Tests\Entities\News')->max('score'));
		$this->assertEquals(1, $dataMapper->orm('Asgard\Orm\Tests\Entities\News')->min('score'));

		#relations cascade
		$this->assertEquals(2, count($dataMapper->related($cat, 'news')->author));
		$this->assertEquals(1, $dataMapper->related($cat, 'news')->author()->where('name', 'Bob')->first()->id);

		#join
		$this->assertEquals(
			2, 
			$dataMapper->orm('Asgard\Orm\Tests\Entities\Author')
			->join('news')
			->where('news.title', '1000th visitor!')
			->first()
			->id
		);

		#next
		$news = [];
		$orm = $dataMapper->orm('Asgard\Orm\Tests\Entities\News');
		while($n = $orm->next())
			$news[] = $n;
		$this->assertEquals(3, count($news));

		#values
		$this->assertEquals(
			['Welcome!', '1000th visitor!', 'Important'],
			$dataMapper->orm('Asgard\Orm\Tests\Entities\News')->orderBy('id ASC')->values('title')
		);

		#ids
		$this->assertEquals(
			[1, 2, 3],
			$dataMapper->orm('Asgard\Orm\Tests\Entities\News')->orderBy('id ASC')->ids()
		);

		#with
		$cats = $dataMapper->orm('Asgard\Orm\Tests\Entities\Category')->with('news')->get();
		$this->assertEquals(1, count($cats[0]->data['properties']['news']));
		$this->assertEquals(2, count($cats[1]->data['properties']['news']));

		$cats = $dataMapper->orm('Asgard\Orm\Tests\Entities\Category')->with('news', function($orm) {
			$orm->with('author');
		})->get();
		$this->assertEquals(1, $cats[0]->data['properties']['news'][0]->data['properties']['author']->id);

		#selectQuery
		$cats = $dataMapper->orm('Asgard\Orm\Tests\Entities\Category')->selectQuery('SELECT * FROM category WHERE title=?', ['General']);
		$this->assertEquals(1, $cats[0]->id);

		#paginate
		$orm = $dataMapper->orm('Asgard\Orm\Tests\Entities\News')->paginate(1, 2);
		$paginator = $orm->getPaginator();
		$this->assertTrue($paginator instanceof \Asgard\Common\Paginator);
		$this->assertEquals(2, count($orm->get()));
		$this->assertEquals(1, count($dataMapper->orm('Asgard\Orm\Tests\Entities\News')->paginate(2, 2)->get()));

		#offset
		$this->assertEquals(3, $dataMapper->orm('Asgard\Orm\Tests\Entities\News')->orderBy('id ASC')->offset(2)->first()->id);

		#limit
		$this->assertCount(2, $dataMapper->orm('Asgard\Orm\Tests\Entities\News')->limit(2)->get());

		#two jointures with the same name
		$r = $dataMapper->orm('Asgard\Orm\Tests\Entities\News')
		->join([
			'author' => 'news n1',
			'category' => 'news n2',
		])
		->where('n2.title', 'Welcome!')
		->get();
		$this->assertCount(2, $r);

		#validation
		$cat = $dataMapper->load('Asgard\Orm\Tests\Entities\Category', 1);
		$this->assertEquals([
			'news' => [
				'morethan' => 'News must have more than 3 elements.'
			]
		], $dataMapper->errors($cat));
		$cat = $em->make('Asgard\Orm\Tests\Entities\Category');
		$this->assertEquals([
			'news' => [
				'relationrequired' => 'News is required.',
				'morethan' => 'News must have more than 3 elements.'
			]
		], $dataMapper->errors($cat));

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
		$authors = Asgard\Orm\Tests\Entities\Categoryi18n::loadByName('general') #from the category "general"
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