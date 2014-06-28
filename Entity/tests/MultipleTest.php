<?php
namespace Asgard\Entity\Tests;

class MultipleTest extends \PHPUnit_Framework_TestCase {
	protected static $app;

	public static function setUpBeforeClass() {
		$app = new \Asgard\Container\Container();
		$app['config'] = new \Asgard\Config\Config();
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
		$this->assertEquals([
			'Bob',
			'Joe',
		], $e->names->all());
	}

	public function testRemove() {
		$e = new Fixtures\EntityMultiple();

		$this->assertInstanceOf('Asgard\Entity\Multiple', $e->names);

		$e->names[] = 'Bob';
		$e->names[] = 'Joe';
		$e->names->remove(0);

		$this->assertEquals([
			'Joe',
		], $e->names->all());

		unset($e->names[0]);
		$this->assertEquals([
		], $e->names->all());
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

		$r = [];
		foreach($e->names as $v)
			$r[] = $v;

		$this->assertEquals([
			'Bob',
			'Joe',
		], $r);
	}

	public function testSerialize() {
		$e = new Fixtures\EntityMultiple();

		$this->assertInstanceOf('Asgard\Entity\Multiple', $e->names);

		$e->names[] = 'Bob';
		$e->names[] = 'Joe';

		$this->assertEquals('a:2:{i:0;s:3:"Bob";i:1;s:3:"Joe";}', $e::getDefinition()->property('names')->serialize($e->names));
	}
}