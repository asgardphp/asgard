<?php
namespace Asgard\Entity\Tests;

class MultipleTest extends \PHPUnit_Framework_TestCase {
	protected static $app;

	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');

		$app = new \Asgard\Core\App();
		$app['config'] = new \Asgard\Core\Config();
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$app['cache'] = new \Asgard\Cache\NullCache;
		$app['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($app);
		\Asgard\Entity\Entity::setApp($app);
		static::$app = $app;
	}

	public function testAdd() {
		$e = new Fixtures\EntityMultiple();

		$this->assertInstanceOf('Asgard\Entity\Multiple', $e->names);

		$e->names[] = 'Bob';
		$e->names->add('Joe');
		$this->assertEquals(array(
			'Bob',
			'Joe',
		), $e->names->all());
	}

	public function testRemove() {
		$e = new Fixtures\EntityMultiple();

		$this->assertInstanceOf('Asgard\Entity\Multiple', $e->names);

		$e->names[] = 'Bob';
		$e->names[] = 'Joe';
		$e->names->remove(0);

		$this->assertEquals(array(
			'Joe',
		), $e->names->all());

		unset($e->names[0]);
		$this->assertEquals(array(
		), $e->names->all());
	}

	public function testGet() {
		$e = new Fixtures\EntityMultiple();

		$this->assertInstanceOf('Asgard\Entity\Multiple', $e->names);

		$e->names[] = 'Bob';
		$e->names[] = 'Joe';

		$this->assertEquals('Joe', $e->names[1]);
		$this->assertEquals('Joe', $e->names->get(1));
	}

	public function testIterate() {
		$e = new Fixtures\EntityMultiple();

		$this->assertInstanceOf('Asgard\Entity\Multiple', $e->names);

		$e->names[] = 'Bob';
		$e->names[] = 'Joe';

		$r = array();
		foreach($e->names as $v)
			$r[] = $v;

		$this->assertEquals(array(
			'Bob',
			'Joe',
		), $r);
	}

	public function testSerialize() {
		$e = new Fixtures\EntityMultiple();

		$this->assertInstanceOf('Asgard\Entity\Multiple', $e->names);

		$e->names[] = 'Bob';
		$e->names[] = 'Joe';

		$this->assertEquals('a:2:{i:0;s:3:"Bob";i:1;s:3:"Joe";}', $e->property('names')->serialize($e->names));
	}
}