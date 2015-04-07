<?php
namespace Asgard\Entity\Tests;

class EntityTest extends \PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		$entityManager = new \Asgard\Entity\EntityManager;
		#set the EntityManager static instance for activerecord-like entities (e.g. new Article or Article::find())
		\Asgard\Entity\EntityManager::setInstance($entityManager);
	}

	public function testChanged() {
		#Fixtures
		$a = new Classes\Comment([
			'content' => 'foo',
			'news' =>  new Classes\News([
				'name' => 'bar',
			])
		]);

		$a->name = 'bar';
		$a->news = new Classes\News([
			'content' => 'bar',
		]);

		$this->assertEquals(['content', 'news'], $a->getChanged());
	}

	public function testToArray() {
		$serializer = new \Asgard\Entity\Serializer;
		$date = new \Asgard\Common\Datetime;

		$news = new Classes\News([
			'title' => 'Test Title',
			'content' => 'Test Content',
			'published' => $date,
			'comments' => [
				new Classes\Comment([
					'content' => 'foo',
					'published' => $date
				]),
				new Classes\Comment([
					'content' => 'bar',
					'published' => $date
				]),
				new Classes\Comment([
					'content' => 'baz',
					'published' => $date
				])
			]
		]);
		$arr = [
			'id' => null,
			'title' => 'Test Title',
			'content' => 'Test Content',
			'published' => $date->format('Y-m-d'),
			'comments' => [
				[
					'id' => null,
					'content' => 'foo',
					'published' => $date->format('Y-m-d'),
					'another_property' => null
				],
				[
					'id' => null,
					'content' => 'bar',
					'published' => $date->format('Y-m-d'),
					'another_property' => null
				],
				[
					'id' => null,
					'content' => 'baz',
					'published' => $date->format('Y-m-d'),
					'another_property' => null
				]
			],
			'another_property' => null
		];
		$arrRaw = [
			'id' => null,
			'title' => 'Test Title',
			'content' => 'Test Content',
			'published' => $date,
			'comments' => [
				[
					'id' => null,
					'content' => 'foo',
					'published' => $date,
					'another_property' => null,
					'news' => null
				],
				[
					'id' => null,
					'content' => 'bar',
					'published' => $date,
					'another_property' => null,
					'news' => null
				],
				[
					'id' => null,
					'content' => 'baz',
					'published' => $date,
					'another_property' => null,
					'news' => null
				]
			],
			'another_property' => null
		];

		#toArrayRaw
		$this->assertEquals(
			$arrRaw,
			$news->toArrayRaw(1)
		);

		#toArray
		$this->assertEquals(
			$arr,
			$news->toArray(1)
		);

		#toJSON
		$this->assertEquals(
			json_encode($arr),
			$news->toJSON(1)
		);

		#arrayToJSON
		$this->assertEquals(
			json_encode([$arr]),
			$serializer->arrayToJSON([$news], 1)
		);


		#I18N
		$newsi18n = new Classes\Newsi18n([
			'title' => 'Test Title',
			'content' => 'Test Content',
			'comments' => [
				(new Classes\Commenti18n([
					'content' => 'foo'
				]))->set('content', 'foofr', 'fr'),
				(new Classes\Commenti18n([
					'content' => 'bar'
				]))->set('content', 'barfr', 'fr'),
				(new Classes\Commenti18n([
					'content' => 'baz'
				]))->set('content', 'bazfr', 'fr'),
			]
		]);
		$newsi18n->set('title', 'Un test', 'fr');
		$arrI18N = [
			'id' => null,
			'title' => [
				'en' => 'Test Title',
				'fr' => 'Un test'
			],
			'content' => 'Test Content',
			'comments' => [
				[
					'id' => null,
					'content' => [
						'en' => 'foo',
						'fr' => 'foofr'
					],
					'another_property' => null
				],
				[
					'id' => null,
					'content' => [
						'en' => 'bar',
						'fr' => 'barfr'
					],
					'another_property' => null
				],
				[
					'id' => null,
					'content' => [
						'en' => 'baz',
						'fr' => 'bazfr'
					],
					'another_property' => null
				]
			],
			'another_property' => null
		];
		$arrRawI18N = [
			'id' => null,
			'title' => [
				'en' => 'Test Title',
				'fr' => 'Un test'
			],
			'content' => 'Test Content',
			'comments' => [
				[
					'id' => null,
					'content' => [
						'en' => 'foo',
						'fr' => 'foofr'
					],
					'another_property' => null
				],
				[
					'id' => null,
					'content' => [
						'en' => 'bar',
						'fr' => 'barfr'
					],
					'another_property' => null
				],
				[
					'id' => null,
					'content' => [
						'en' => 'baz',
						'fr' => 'bazfr'
					],
					'another_property' => null
				]
			],
			'another_property' => null
		];

		#toArrayRawI18N
		$this->assertEquals(
			$arrRawI18N,
			$newsi18n->toArrayRawI18N([], 1)
		);

		#toArrayI18N
		$this->assertEquals(
			$arrI18N,
			$newsi18n->toArrayI18N([], 1)
		);

		#toJSONI18N
		$this->assertEquals(
			json_encode($arrI18N),
			$newsi18n->toJSONI18N([], 1)
		);

		#arrayToJSONI18N
		$this->assertEquals(
			json_encode([$arrI18N]),
			$serializer->arrayToJSONI18N([$newsi18n], [], 1)
		);
	}

	public function testEntitiesWithDependencyInjection() {
		$container = new \Asgard\Container\Container;
		$container['hooks'] = new \Asgard\Hook\HookManager($container);
		$em = new \Asgard\Entity\EntityManager($container);
		$news = $em->make('Asgard\Entity\Tests\Classes\News', [
			'title' => 'Test Title',
			'content' => 'Test Content',
			'published' => \Asgard\Common\Datetime::create(2009, 9, 9),
		]);
		$this->assertEquals('Test Title', $news->title);
		$this->assertEquals('Test Content', $news->content);
	}

	public function testActiveRecordEntities() {
		$news = new Classes\News([
			'title' => 'Test Title',
			'content' => 'Test Content'
		]);
		$this->assertEquals('Test Title', $news->title);
		$this->assertEquals('Test Content', $news->content);
	}

	public function test2() {
		$serializer = new \Asgard\Entity\Serializer;
		$news = new Classes\News([
			'title' => 'Test Title',
			'content' => 'Test Content',
			'published' => \Asgard\Common\Datetime::create(2009, 9, 9),
		]);
		$this->assertEquals('Test Title', $news->title);
		$this->assertEquals('Test Content', $news->content);
		$this->assertTrue(isset($news->title));
		$news->title = 'bla';
		$this->assertEquals('bla', $news->title);
		unset($news->title);
		$this->assertNull($news->title);

		#hook call static
		$this->assertEquals('bla', Classes\News::test1());

		#hook call
		$news->title = 'bla';
		$this->assertEquals('bla', $news->test2());

		#configure
		$definition = Classes\News::getStaticDefinition();
		$this->assertTrue($definition->hasProperty('another_property'));

		#i18n
		$this->assertTrue(Classes\Newsi18n::isI18N());
		$this->assertFalse(Classes\News::isI18N());

		#property
		$property = Classes\News::property('title');
		$this->assertTrue($property instanceof \Asgard\Entity\Properties\StringProperty);
		$this->assertEquals('title', $property->getName());
		$this->assertEquals('string', $property->get('type'));

		#properties
		$properties = Classes\News::properties();
		$this->assertTrue(is_array($properties));
		$this->assertEquals($properties['title'], $property);

		#getEntityName
		$this->assertEquals('news', Classes\News::getShortName());

		#toJSON
		$this->assertEquals('{"id":null,"title":"bla","content":"Test Content","published":"2009-09-09","another_property":null}', $news->toJSON());

		#toArray
		$this->assertEquals(
			[
				'id' => null,
				'title' => 'bla',
				'content' => 'Test Content',
				'published' => '2009-09-09',
				'another_property' => ''
			],
			$news->toArray()
		);

		#toArrayRaw
		$newsArray = $news->toArrayRaw();
		unset($newsArray['published']);
		$this->assertEquals(
			[
				'id' => null,
				'title' => 'bla',
				'content' => 'Test Content',
				#'published' => \Asgard\Common\Datetime::create(2009, 9, 9),
				'another_property' => null,
				'comments' => null
			],
			$newsArray
		);

		#arrayToJSON
		$news = [
			new Classes\News([
				'title' => 'Title 1',
				'content' => 'Content 1',
				'published' => \Asgard\Common\Datetime::create(2009, 9, 9),
			]),
			new Classes\News([
				'title' => 'Title 2',
				'content' => 'Content 2',
				'published' => \Asgard\Common\Datetime::create(2009, 9, 9),
			]),
			new Classes\News([
				'title' => 'Title 3',
				'content' => 'Content 3',
				'published' => \Asgard\Common\Datetime::create(2009, 9, 9),
			]),
		];
		$this->assertEquals(
			'[{"id":null,"title":"Title 1","content":"Content 1","published":"2009-09-09","another_property":null},{"id":null,"title":"Title 2","content":"Content 2","published":"2009-09-09","another_property":null},{"id":null,"title":"Title 3","content":"Content 3","published":"2009-09-09","another_property":null}]',
			$serializer->arrayToJSON($news)
		);

		#valid
		$news = new Classes\News([
			'title' => 'Test Title',
			'content' => 'Test Content',
		]);
		$this->assertTrue($news->valid());
		$news = new Classes\News([
			'title' => null,
			'content' => 'Test Content',
		]);
		$this->assertFalse($news->valid());

		#errors
		$this->assertEquals(
			[
				'title' => [
					'required' => 'Title is required.'
				]
			],
			$news->errors()
		);

		#set/get
		$news = new Classes\News([
			'title' => 'Test Title',
		]);
		$this->assertEquals('Test Title', $news->title);
		$news->title = 'bla';
		$this->assertEquals('bla', $news->title);

		$this->assertNull($news->something);
		$news->something = 'bla';
		$this->assertEquals('bla', $news->something);

		#setHook
		$news = new Classes\NewsHook([
			'title' => 'Test Title',
		]);
		$this->assertEquals(strrev('Test Title'), $news->title);
	}
}