<?php
namespace Asgard\Hook\Tests;

use \Asgard\Hook\HookManager;
use \Asgard\Hook\Chain;
use \Asgard\Hook\HookableTrait;
use \Asgard\Hook\HookContainer;

class HookTest extends \PHPUnit_Framework_TestCase {
	public function testHook() {
		$hooks = new HookManager;

		$hooks->hook('test', function() {
			return 'hello';
		});
		$this->assertEquals('hello', $hooks->trigger('test'));

		$hooks->preHook('test', function() {
			return 'yo';
		});
		$this->assertEquals('yo', $hooks->trigger('test'));

		$mock = $this->getMock('StdClass', ['on', 'post']);
		$mock->expects($this->once())->method('on');
		$mock->expects($this->once())->method('post');
		$hooks->hook('foo', [$mock, 'on']);
		$hooks->postHook('foo', [$mock, 'post']);
		$hooks->trigger('foo');

		$this->assertCount(2, $hooks->get('foo'));
	}

	public function testHooks() {
		$hooks = new HookManager;

		$mock = $this->getMock('StdClass', ['on', 'post']);
		$mock->expects($this->once())->method('on');
		$mock->expects($this->once())->method('post');

		$hooks->hooks([
			'foo' => [
				[$mock, 'on'],
				[$mock, 'post'],
			]
		]);
		$hooks->trigger('foo');
	}

	public function testExecuted() {
		$hooks = new HookManager;

		$hooks->hooks([
			'foo' => [
				function() { },
				function() { },
			]
		]);
		$hooks->trigger('foo', [], null, $chain);
		$this->assertEquals(2, $chain->executed());
	}

	public function testChainStop() {
		$hooks = new HookManager;

		$hooks->hooks([
			'foo' => [
				function($chain) { $chain->stop(); },
				function() { },
			]
		]);
		$hooks->trigger('foo', [], null, $chain);
		$this->assertEquals(1, $chain->executed());
	}

	public function testHookContainer() {
		$hooks = new HookManager();

		$AnnotationReader = new \Asgard\Hook\AnnotationReader;
		$fhooks = $AnnotationReader->fetchHooks('Asgard\Hook\Tests\Fixtures\Hooks');

		$hooks->hooks($fhooks);

		$this->assertEquals('bar', $hooks->trigger('test'));
	}

	public function testHookable() {
		$foo = new Foo;
		$foo->hook('foo', function() { return 'plplp'; });
		$this->assertEquals('plplp', $foo->trigger('foo'));
	}
}

class Foo {
	use HookableTrait;
}