<?php
namespace Asgard\Entity\Tests;

class EntityTest extends \PHPUnit_Framework_TestCase {
	protected static $app;

	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');

		$app = new \Asgard\Core\App;
		$app['config'] = new \Asgard\Core\Config;
		$app['config']->set('locale', 'en');
		$app['config']->set('locales', array('fr', 'en'));
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$app['cache'] = new \Asgard\Cache\NullCache;
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
		$news = new Classes\News(array(
			'title' => 'Test Title',
			'content' => 'Test Content',
			'published' => \Carbon\Carbon::create(2009, 9, 9),
		));
		$this->assertEquals('Test Title', $news->title);
		$this->assertEquals('Test Content', $news->content);
		$this->assertTrue(isset($news->title));
		$news->title = 'bla';
		$this->assertEquals('bla', $news->title);
		unset($news->title);
		$this->assertNull($news->title);

		#hook call static
		$definition = Classes\News::getDefinition();
		$this->assertEquals('bla', Classes\News::test1());

		#hook call
		$definition = Classes\News::getDefinition();
		$news->title = 'bla';
		$this->assertEquals('bla', $news->test2());

		#configure
		$this->assertTrue($definition->hasProperty('another_property'));

		#i18n
		$this->assertTrue(Classes\Newsi18n::isI18N());
		$this->assertFalse(Classes\News::isI18N());

		#property
		$property = Classes\News::property('title');
		$this->assertTrue($property instanceof \Asgard\Entity\Properties\TextProperty);
		$this->assertEquals('title', $property->getName());
		$this->assertEquals('text', $property->type);

		#properties
		$properties = Classes\News::properties();
		$this->assertTrue(is_array($properties));
		$this->assertEquals($properties['title'], $property);

		#propertyNames
		$names = Classes\News::propertyNames();
		$this->assertContains('title', $names);
		$this->assertContains('content', $names);

		#getEntityName
		$this->assertEquals('news', Classes\News::getShortName());

		#toJSON
		$json = $news->toJSON();
		$this->assertEquals('{"title":"bla","content":"Test Content","published":"2009-09-09","another_property":""}', $news->toJSON());

		#toArray
		$this->assertEquals(
			array(
				'title' => 'bla',
				'content' => 'Test Content',
				'published' => '2009-09-09',
				'another_property' => ''
			),
			$news->toArray()
		);

		#toArrayRaw
		$newsArray = $news->toArrayRaw();
		unset($newsArray['published']);
		$this->assertEquals(
			array(
				'title' => 'bla',
				'content' => 'Test Content',
				// 'published' => \Carbon\Carbon::create(2009, 9, 9),
				'another_property' => ''
			),
			$newsArray
		);

		#arrayToJSON
		$news = array(
			new Classes\News(array(
				'title' => 'Title 1',
				'content' => 'Content 1',
				'published' => \Carbon\Carbon::create(2009, 9, 9),
			)),
			new Classes\News(array(
				'title' => 'Title 2',
				'content' => 'Content 2',
				'published' => \Carbon\Carbon::create(2009, 9, 9),
			)),
			new Classes\News(array(
				'title' => 'Title 3',
				'content' => 'Content 3',
				'published' => \Carbon\Carbon::create(2009, 9, 9),
			)),
		);
		$this->assertEquals(
			'[{"title":"Title 1","content":"Content 1","published":"2009-09-09","another_property":""},{"title":"Title 2","content":"Content 2","published":"2009-09-09","another_property":""},{"title":"Title 3","content":"Content 3","published":"2009-09-09","another_property":""}]',
			Classes\News::arrayToJSON($news)
		);

		#valid
		$news = new Classes\News(array(
			'title' => 'Test Title',
			'content' => 'Test Content',
		));
		$this->assertTrue($news->valid());
		$news = new Classes\News(array(
			'title' => null,
			'content' => 'Test Content',
		));
		$this->assertFalse($news->valid());

		#errors
		$this->assertEquals(
			array(
				'title' => array(
					'required' => 'Title is required.'
				)
			),
			$news->errors()
		);

		#set/get
		$news = new Classes\News(array(
			'title' => 'Test Title',
		));
		$this->assertEquals('Test Title', $news->title);
		$news->title = 'bla';
		$this->assertEquals('bla', $news->title);

		$this->assertNull($news->something);
		$news->something = 'bla';
		$this->assertEquals('bla', $news->something);

		$news = new Classes\Newsi18n;
		$news->title = 'English Title';
		$this->assertEquals('English Title', $news->title);
		$news->set('title', 'Titre Français', 'fr');
		// d($news->get('title', 'en'));
		$this->assertEquals('English Title', $news->get('title', 'en'));
		$this->assertEquals('Titre Français', $news->get('title', 'fr'));
		$this->assertEquals(
			array(
				'fr' => 'Titre Français',
				'en' => 'English Title',
			),
			$news->get('title', 'all')
		);

		$news->set('title', array(
			'fr' => 'Autre titre',
			'en' => 'Another title',
		), 'all');
		$this->assertEquals(
			array(
				'fr' => 'Autre titre',
				'en' => 'Another title',
			),
			$news->get('title', 'all')
		);

		#setHook
		$news = new Classes\NewsHook(array(
			'title' => 'Test Title',
		));
		$this->assertEquals(strrev('Test Title'), $news->title);
	}
}