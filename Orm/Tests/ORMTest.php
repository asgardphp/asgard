<?php
class ORMTest extends PHPUnit_Framework_TestCase {
	protected static $app;

	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');

		$app = new \Asgard\Core\App;
		$app['hook'] = new \Asgard\Hook\Hook($app);
		$app['config'] = new \Asgard\Core\Config;
		$app['cache'] = new \Asgard\Cache\NullCache;
		$app->register('paginator', function($app, $args) {
			return new \Asgard\Utils\Paginator($args[0], $args[1], $args[2]);
		});
		$app['rulesregistry'] = new \Asgard\Validation\RulesRegistry;
		$app['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($app);
		$app['db'] = new \Asgard\Db\DB(array(
			'database' => 'asgard',
			'user' => 'root',
			'password' => '',
			'host' => 'localhost'
		));
		\Asgard\Entity\Entity::setApp($app);
		static::$app = $app;
	}
	
	public function test1() {
		static::$app['db']->import(realpath(__dir__.'/sql/ormtest.sql'));

		#load
		$cat = Asgard\Orm\Tests\Entities\Category::load(1);
		$this->assertEquals(1, $cat->id);
		$this->assertEquals('General', $cat->title);

		#relation count
		$this->assertEquals(2, $cat->news()->count());

		#orderBy
		$this->assertEquals(2, Asgard\Orm\Tests\Entities\Category::first()->id); #default order is id DESC
		$this->assertEquals(2, Asgard\Orm\Tests\Entities\Category::orderBy('id DESC')->first()->id);
		$this->assertEquals(1, Asgard\Orm\Tests\Entities\Category::orderBy('id ASC')->first()->id);

		#relation shortcut
		$this->assertEquals(2, count($cat->news));

		#relation + where
		$this->assertEquals(1, $cat->news()->where('title', 'Welcome!')->first()->id);

		#joinToEntity
		$this->assertEquals(
			1, 
			Asgard\Orm\Tests\Entities\News::joinToEntity('category', $cat)->where('title', 'Welcome!')->first()->id
		);
		$author = Asgard\Orm\Tests\Entities\Category::load(2);
		$this->assertEquals(
			2, 
			Asgard\Orm\Tests\Entities\News::joinToEntity('category', $cat)->joinToEntity('author', $author)->where('author.name', 'Joe')->first()->id
		);
		$this->assertEquals(
			null,
			Asgard\Orm\Tests\Entities\News::joinToEntity('category', $cat)->joinToEntity('author', $author)->where('author.name', 'Bob')->first()
		);

		#stats functions
		$this->assertEquals(2.6667, Asgard\Orm\Tests\Entities\News::avg('score'));
		$this->assertEquals(8, Asgard\Orm\Tests\Entities\News::sum('score'));
		$this->assertEquals(5, Asgard\Orm\Tests\Entities\News::max('score'));
		$this->assertEquals(1, Asgard\Orm\Tests\Entities\News::min('score'));

		#relations cascade
		$this->assertEquals(2, count($cat->news()->author));
		$this->assertEquals(1, $cat->news()->author()->where('name', 'Bob')->first()->id);

		#join
		$this->assertEquals(
			2, 
			Asgard\Orm\Tests\Entities\Author::orm()
			->join('news')
			->where('news.title', '1000th visitor!')
			->first()
			->id
		);

		#next
		$news = array();
		$orm = Asgard\Orm\Tests\Entities\News::orm();
		while($n = $orm->next())
			$news[] = $n;
		$this->assertEquals(3, count($news));

		#values
		$this->assertEquals(
			array('Welcome!', '1000th visitor!', 'Important'),
			Asgard\Orm\Tests\Entities\News::orderBy('id ASC')->values('title')
		);

		#ids
		$this->assertEquals(
			array(1, 2, 3),
			Asgard\Orm\Tests\Entities\News::orderBy('id ASC')->ids()
		);

		#with
		$cats = Asgard\Orm\Tests\Entities\Category::with('news')->get();
		$this->assertEquals(1, count($cats[0]->data['news']));
		$this->assertEquals(2, count($cats[1]->data['news']));

		$cats = Asgard\Orm\Tests\Entities\Category::with('news', function($orm) {
			$orm->with('author');
		})->get();
		$this->assertEquals(1, $cats[0]->data['news'][0]->data['author']->id);

		#selectQuery
		$cats = Asgard\Orm\Tests\Entities\Category::selectQuery('SELECT * FROM category WHERE title=?', array('General'));
		$this->assertEquals(1, $cats[0]->id);

		#paginate
		$orm = Asgard\Orm\Tests\Entities\News::paginate(1, 2);
		$paginator = $orm->getPaginator();
		$this->assertTrue($paginator instanceof \Asgard\Utils\Paginator);
		$this->assertEquals(2, count($orm->get()));
		$this->assertEquals(1, count(Asgard\Orm\Tests\Entities\News::paginate(2, 2)->get()));

		#offset
		$this->assertEquals(3, Asgard\Orm\Tests\Entities\News::orderBy('id ASC')->offset(2)->first()->id);

		#limit
		$this->assertEquals(2, count(Asgard\Orm\Tests\Entities\News::limit(2)->get()));

		#two jointures with the same name
		$r = Asgard\Orm\Tests\Entities\News::first()
		->join(array(
			'author' => 'news n1',
			'category' => 'news n2',
		))
		->where('n2.title', 'Welcome!')
		->get();
		$this->assertCount(2, $r);

		#validation
		static::$app['rulesregistry']->registerNamespace('Asgard\Orm\Validation');
		$cat = Asgard\Orm\Tests\Entities\Category::load(1);
		$this->assertEquals(array(
			'news' => array(
				'morethan' => 'News must have more than 3 elements.'
			)
		), $cat->errors());
		$cat = new Asgard\Orm\Tests\Entities\Category;
		$this->assertEquals(array(
			'news' => array(
				'relationrequired' => 'News is required.',
				'morethan' => 'News must have more than 3 elements.'
			)
		), $cat->errors());

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

		#get all the authors in page 1 (10 per page), which belong to news that have score > 3 and belongs to category "general", and with their comments, and all in english only.
		$authors = Asgard\Orm\Tests\Entities\Categoryi18n::loadByName('general') #from the category "general"
		->news() #get the news
		->where('score > 3') #whose score is greater than 3
		->author() #the authors from the previous news
		->with(array( #along with their comments
			'comments'
		))
		->paginate(1, 10) #paginate, 10 authors per page, page 1
		->get();
	}
}