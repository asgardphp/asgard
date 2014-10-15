<?php
namespace Asgard\Entity\Tests;

class MultipleTest extends \PHPUnit_Framework_TestCase {
	protected static $container;

	public static function setUpBeforeClass() {
		$container = new \Asgard\Container\Container;
		$container['hooks'] = new \Asgard\Hook\HookManager($container);

		$entityManager = $container['entityManager'] = new \Asgard\Entity\EntityManager($container);
		#set the EntityManager static instance for activerecord-like entities (e.g. new Article or Article::find())
		\Asgard\Entity\EntityManager::setInstance($entityManager);

		static::$container = $container;
	}

	public function testAdd() {
		$e = new Fixtures\EntityMultiple();

		$this->assertInstanceOf('Asgard\Entity\ManyCollection', $e->names);

		$e->names[] = 'Bob';
		$e->names->add('Joe');
		$this->assertEquals([
			'Bob',
			'Joe',
		], $e->names->all());
	}

	public function testRemove() {
		$e = new Fixtures\EntityMultiple();

		$this->assertInstanceOf('Asgard\Entity\ManyCollection', $e->names);

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

		$this->assertInstanceOf('Asgard\Entity\ManyCollection', $e->names);

		$e->names[] = 'Bob';
		$e->names[] = 'Joe';

		$this->assertEquals('Joe', $e->names[1]);
		$this->assertEquals('Joe', $e->names->get(1));
	}

	public function testIterate() {
		$e = new Fixtures\EntityMultiple();

		$this->assertInstanceOf('Asgard\Entity\ManyCollection', $e->names);

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

		$this->assertInstanceOf('Asgard\Entity\ManyCollection', $e->names);

		$e->names[] = 'Bob';
		$e->names[] = 'Joe';

		$this->assertEquals('a:2:{i:0;s:3:"Bob";i:1;s:3:"Joe";}', $e::getStaticDefinition()->property('names')->serialize($e->names));
	}
}