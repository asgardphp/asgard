<?php
namespace Asgard\Container\Tests;

class ContainerTest extends \PHPUnit_Framework_TestCase {
	public function testInstance() {
		$this->assertInstanceOf('Asgard\Container\Container', \Asgard\Container\Container::singleton());
	}

	public function testAutofacade() {
		$app = \Asgard\Container\Container::singleton();
		$app->setAutofacade(true);

		$this->assertFalse(class_exists('Test'));
		$app['test'] = new Fixtures\Foo;
		$this->assertTrue(class_exists('Test'));
		$this->assertEquals('bar', \Test::test());

		$this->assertFalse(class_exists('Test2'));
		$app->register('test2', function() { return new Fixtures\Foo; });
		$this->assertTrue(class_exists('Test2'));
	}

	public function testSetGet() {
		$app = new \Asgard\Container\Container;
		$app['test'] = '1245';
		$this->assertEquals('1245', $app['test']);
	}

	public function testRegisterAndMake() {
		$app = new \Asgard\Container\Container;
		$app->register('test', function() {
			return new \StdClass;
		});
		$first = $app->make('test');
		$second = $app->make('test');
		$this->assertInstanceOf('StdClass', $first);
		$this->assertInstanceOf('StdClass', $second);
		$this->assertFalse($first === $second);

		$app->register('test2', function() {
			return new \StdClass;
		});
		$this->assertInstanceOf('StdClass', $app['test2']);
	}

	public function testMakeDefault() {
		$app = new \Asgard\Container\Container;
		$this->assertInstanceOf('StdClass', $app->make('test', [], function() { return new \StdClass; }));
	}

	public function testHas() {
		$app = new \Asgard\Container\Container;
		$app['test1'] = 123;
		$app->register('test3', function() { return 123; });
		$this->assertTrue($app->has('test1'));
		$this->assertFalse($app->has('test2'));
		$this->assertTrue($app->has('test3'));
	}

	public function testRemove() {
		$app = new \Asgard\Container\Container;
		
		$app['test'] = 123;
		$this->assertTrue($app->has('test'));
		$app->remove('test');
		$this->assertFalse($app->has('test'));

		$app['test'] = 123;
		$this->assertTrue($app->has('test'));
		unset($app['test']);
		$this->assertFalse(isset($app['test']));
	}
}