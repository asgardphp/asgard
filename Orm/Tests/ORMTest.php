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
		$container->register('datamapper', function($container) {
			return new \Asgard\Orm\DataMapper(
				$container['db'],
				'en',
				'',
				$container->createFactory('orm'),
				$container->createFactory('collectionOrm')
			);
		});

		$entitiesManager = $container['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($container);
		$entitiesManager->setValidatorFactory($container->createFactory('validator'));
		#set the EntitiesManager static instance for activerecord-like entities (e.g. new Article or Article::find())
		\Asgard\Entity\EntitiesManager::setInstance($entitiesManager);

		static::$container = $container;
	}
	
	public function test1() {
		$db = static::$container['db'];
		$db->query('DROP TABLE IF EXISTS `category`;');
		$db->query('CREATE TABLE `category` (
		  `id` tinyint NOT NULL PRIMARY KEY,
		  `title` varchar(255) NOT NULL,
		  `description` varchar(255) NOT NULL
		);');
		$db->query('INSERT INTO `category` (`id`, `title`, `description`) VALUES (\'1\', \'General\', \'General news\');');
		$db->query('INSERT INTO `category` (`id`, `title`, `description`) VALUES (\'2\', \'Misc\', \'Other news\');');

		$db->query('DROP TABLE IF EXISTS `news`;');
		$db->query('CREATE TABLE `news` (
		  `id` tinyint NOT NULL PRIMARY KEY,
		  `title` varchar(255) NOT NULL,
		  `content` varchar(255) NOT NULL,
		  `category_id` int(11) NOT NULL,
		  `author_id` int(11) NOT NULL,
		  `score` int(11) NOT NULL
		);');
		$db->query('INSERT INTO `news` (`id`, `title`, `content`, `category_id`, `author_id`, `score`) VALUES (\'1\', \'Welcome!\', \'blabla\', 1, 1, 2);');
		$db->query('INSERT INTO `news` (`id`, `title`, `content`, `category_id`, `author_id`, `score`) VALUES (\'2\', \'1000th visitor!\', \'blabla\', 1, 2, 5);');
		$db->query('INSERT INTO `news` (`id`, `title`, `content`, `category_id`, `author_id`, `score`) VALUES (\'3\', \'Important\', \'blabla\', 2, 1, 1);');

		$db->query('DROP TABLE IF EXISTS `author`;');
		$db->query('CREATE TABLE `author` (
		  `id` tinyint NOT NULL PRIMARY KEY,
		  `name` varchar(255) NOT NULL
		);');
		$db->query('INSERT INTO `author` (`id`, `name`) VALUES (\'1\', \'Bob\');');
		$db->query('INSERT INTO `author` (`id`, `name`) VALUES (\'2\', \'Joe\');');
		$db->query('INSERT INTO `author` (`id`, `name`) VALUES (\'3\', \'John\');');

		#load
		$cat = Entities\Category::load(1);
		$this->assertEquals(1, $cat->id);
		$this->assertEquals('General', $cat->title);

		#relation count
		$this->assertEquals(2, $cat->news()->count());

		#orderBy
		$this->assertEquals(2, Entities\Category::first()->id); #default order is id DESC
		$this->assertEquals(2, Entities\Category::orderBy('id DESC')->first()->id);
		$this->assertEquals(1, Entities\Category::orderBy('id ASC')->first()->id);

		#relation shortcut
		$this->assertEquals(2, count($cat->news));

		#relation + where
		$this->assertEquals(1, $cat->news()->where('title', 'Welcome!')->first()->id);

		#joinToEntity
		$this->assertEquals(
			1, 
			Entities\News::joinToEntity('category', $cat)->where('title', 'Welcome!')->first()->id
		);
		$author = Entities\Category::load(2);
		$this->assertEquals(
			2, 
			Entities\News::joinToEntity('category', $cat)->joinToEntity('author', $author)->where('author.name', 'Joe')->first()->id
		);
		$this->assertEquals(
			null,
			Entities\News::joinToEntity('category', $cat)->joinToEntity('author', $author)->where('author.name', 'Bob')->first()
		);

		#stats functions
		$this->assertEquals(26, floor(Entities\News::avg('score')*10));
		$this->assertEquals(8, Entities\News::sum('score'));
		$this->assertEquals(5, Entities\News::max('score'));
		$this->assertEquals(1, Entities\News::min('score'));

		#relations cascade
		$this->assertEquals(2, count($cat->news()->author));
		$this->assertEquals(1, $cat->news()->author()->where('name', 'Bob')->first()->id);

		#join
		$this->assertEquals(
			2, 
			Entities\Author::orm()
			->join('news')
			->where('news.title', '1000th visitor!')
			->first()
			->id
		);

		#next
		$news = [];
		$orm = Entities\News::orm();
		while($n = $orm->next())
			$news[] = $n;
		$this->assertEquals(3, count($news));

		#values
		$this->assertEquals(
			['Welcome!', '1000th visitor!', 'Important'],
			Entities\News::orderBy('id ASC')->values('title')
		);

		#ids
		$this->assertEquals(
			[1, 2, 3],
			Entities\News::orderBy('id ASC')->ids()
		);

		#with
		$cats = Entities\Category::with('news')->get();
		$this->assertEquals(1, count($cats[0]->data['news']));
		$this->assertEquals(2, count($cats[1]->data['news']));

		$cats = Entities\Category::with('news', function($orm) {
			$orm->with('author');
		})->get();
		$this->assertEquals(1, $cats[0]->data['news'][0]->data['author']->id);

		#selectQuery
		$cats = Entities\Category::selectQuery('SELECT * FROM category WHERE title=?', ['General']);
		$this->assertEquals(1, $cats[0]->id);

		#paginate
		$orm = Entities\News::paginate(1, 2);
		$paginator = $orm->getPaginator();
		$this->assertTrue($paginator instanceof \Asgard\Common\Paginator);
		$this->assertEquals(2, count($orm->get()));
		$this->assertEquals(1, count(Entities\News::paginate(2, 2)->get()));

		#offset
		$this->assertEquals(3, Entities\News::orderBy('id ASC')->offset(2)->first()->id);

		#limit
		$this->assertEquals(2, count(Entities\News::limit(2)->get()));

		#two jointures with the same name
		$r = Entities\News::first()
		->join([
			'author' => 'news n1',
			'category' => 'news n2',
		])
		->where('n2.title', 'Welcome!')
		->get();
		$this->assertCount(2, $r);

		#validation
		static::$container['rulesregistry']->registerNamespace('Asgard\Orm\Rules');
		$cat = Entities\Category::load(1);
		$this->assertEquals([
			'news' => [
				'morethan' => 'News must have more than 3 elements.'
			]
		], $cat->errors());
		$cat = new Entities\Category;
		$this->assertEquals([
			'news' => [
				'relationrequired' => 'News is required.',
				'morethan' => 'News must have more than 3 elements.'
			]
		], $cat->errors());

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