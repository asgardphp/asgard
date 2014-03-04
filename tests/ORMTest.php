<?php
class ORMTest extends PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
		require_once(_CORE_DIR_.'core.php');
		\Coxis\Core\App::instance(true)->config->set('bundles', array(
			_COXIS_DIR_.'core',
			_COXIS_DIR_.'orm',
		));
		\Coxis\Core\App::loadDefaultApp();
	}
	
	public function test1() {
		\Coxis\Core\App::get('db')->import(realpath(dirname(__FILE__).'/sql/test1.sql'));

		#load
		$cat = Coxis\ORM\Tests\Entities\Category::load(1);
		$this->assertEquals(1, $cat->id);
		$this->assertEquals('General', $cat->title);

		#relation count
		$this->assertEquals(2, $cat->news()->count());

		#orderBy
		$this->assertEquals(2, Coxis\ORM\Tests\Entities\Category::first()->id); #default order is id DESC
		$this->assertEquals(2, Coxis\ORM\Tests\Entities\Category::orderBy('id DESC')->first()->id);
		$this->assertEquals(1, Coxis\ORM\Tests\Entities\Category::orderBy('id ASC')->first()->id);

		#relation shortcut
		$this->assertEquals(2, sizeof($cat->news));

		#relation + where
		$this->assertEquals(1, $cat->news()->where('title', 'Welcome!')->first()->id);

		#joinToEntity
		$this->assertEquals(
			1, 
			Coxis\ORM\Tests\Entities\News::joinToEntity('category', $cat)->where('title', 'Welcome!')->first()->id
		);
		$author = Coxis\ORM\Tests\Entities\Category::load(2);
		$this->assertEquals(
			2, 
			Coxis\ORM\Tests\Entities\News::joinToEntity('category', $cat)->joinToEntity('author', $author)->where('author.name', 'Joe')->first()->id
		);
		$this->assertEquals(
			null,
			Coxis\ORM\Tests\Entities\News::joinToEntity('category', $cat)->joinToEntity('author', $author)->where('author.name', 'Bob')->first()
		);
		#todo provide only id for entity
		/*$this->assertEquals(
			2, 
			Coxis\ORM\Tests\Entities\News::joinToEntity('category', 1)->joinToEntity('author', 2)->where('author.name', 'Joe')->first()->id
		);*/

		#stats functions
		$this->assertEquals(2.6667, Coxis\ORM\Tests\Entities\News::avg('score'));
		$this->assertEquals(8, Coxis\ORM\Tests\Entities\News::sum('score'));
		$this->assertEquals(5, Coxis\ORM\Tests\Entities\News::max('score'));
		$this->assertEquals(1, Coxis\ORM\Tests\Entities\News::min('score'));

		#relations cascade
		$this->assertEquals(2, sizeof($cat->news()->author));
		$this->assertEquals(1, $cat->news()->author()->where('name', 'Bob')->first()->id);

		#join
		$this->assertEquals(
			2, 
			Coxis\ORM\Tests\Entities\Author::orm()
			->join('news')
			->where('news.title', '1000th visitor!')
			->first()
			->id
		);
		#todo probleme si deux relations s'appellent "news"
			/*Coxis\ORM\Tests\Entities\Author::orm()
			->join('news')
			->join(array(
				'comments' => 'news'
			)
			->where('news.title', '1000th visitor!')
			->first()
			->id*/

		#next
		$news = array();
		$orm = Coxis\ORM\Tests\Entities\News::orm();
		while($n = $orm->next())
			$news[] = $n;
		$this->assertEquals(3, sizeof($news));

		#values
		$this->assertEquals(
			array('Welcome!', '1000th visitor!', 'Important'),
			Coxis\ORM\Tests\Entities\News::orderBy('id ASC')->values('title')
		);

		#ids
		$this->assertEquals(
			array(1, 2, 3),
			Coxis\ORM\Tests\Entities\News::orderBy('id ASC')->ids()
		);

		#with
		$cats = Coxis\ORM\Tests\Entities\Category::with('news')->get();
		$this->assertEquals(1, sizeof($cats[0]->data['news']));
		$this->assertEquals(2, sizeof($cats[1]->data['news']));

		$cats = Coxis\ORM\Tests\Entities\Category::with('news', function($orm) {
			$orm->with('author');
		})->get();
		$this->assertEquals(1, $cats[0]->data['news'][0]->data['author']->id);

		#selectQuery
		$cats = Coxis\ORM\Tests\Entities\Category::selectQuery('SELECT * FROM category WHERE title=?', array('General'));
		$this->assertEquals(1, $cats[0]->id);

		#paginate
		$orm = Coxis\ORM\Tests\Entities\News::paginate(1, 2);
		$paginator = $orm->getPaginator();
		$this->assertTrue($paginator instanceof \Coxis\Utils\Paginator);
		$this->assertEquals(2, sizeof($orm->get()));
		$this->assertEquals(1, sizeof(Coxis\ORM\Tests\Entities\News::paginate(2, 2)->get()));

		#offset
		$this->assertEquals(3, Coxis\ORM\Tests\Entities\News::orderBy('id ASC')->offset(2)->first()->id);

		#limit
		$this->assertEquals(2, sizeof(Coxis\ORM\Tests\Entities\News::limit(2)->get()));

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
		$authors = Coxis\ORM\Tests\Entities\Categoryi18n::loadByName('general') #from the category "general"
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