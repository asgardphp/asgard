<?php
namespace Asgard\Container\Tests;

class ContainerTest extends \PHPUnit_Framework_TestCase {
	public function testInstance() {
		$this->assertInstanceOf('Asgard\Container\Container', \Asgard\Container\Container::singleton());
	}

	public function testAutofacade() {
		$container = \Asgard\Container\Container::singleton();
		$container->setAutofacade(true);

		$this->assertFalse(class_exists('Test'));
		$container['test'] = new Fixtures\Foo;
		$this->assertTrue(class_exists('Test'));
		$this->assertEquals('bar', \Test::test());

		$this->assertFalse(class_exists('Test2'));
		$container->register('test2', function() { return new Fixtures\Foo; });
		$this->assertTrue(class_exists('Test2'));
	}

	public function testSetGet() {
		$container = new \Asgard\Container\Container;
		$container['test'] = '1245';
		$this->assertEquals('1245', $container['test']);
	}

	public function testRegisterAndMake() {
		$container = new \Asgard\Container\Container;
		$container->register('test', function() {
			return new \StdClass;
		});
		$first = $container->make('test');
		$second = $container->make('test');
		$this->assertInstanceOf('StdClass', $first);
		$this->assertInstanceOf('StdClass', $second);
		$this->assertFalse($first === $second);

		$container->register('test2', function() {
			return new \StdClass;
		});
		$this->assertInstanceOf('StdClass', $container['test2']);
	}

	public function testMakeDefault() {
		$container = new \Asgard\Container\Container;
		$this->assertInstanceOf('StdClass', $container->make('test', [], function() { return new \StdClass; }));
	}

	public function testHas() {
		$container = new \Asgard\Container\Container;
		$container['test1'] = 123;
		$container->register('test3', function() { return 123; });
		$this->assertTrue($container->has('test1'));
		$this->assertFalse($container->has('test2'));
		$this->assertTrue($container->has('test3'));
	}

	public function testRemove() {
		$container = new \Asgard\Container\Container;

		$container['test'] = 123;
		$this->assertTrue($container->has('test'));
		$container->remove('test');
		$this->assertFalse($container->has('test'));

		$container['test'] = 123;
		$this->assertTrue($container->has('test'));
		unset($container['test']);
		$this->assertFalse(isset($container['test']));
	}
}