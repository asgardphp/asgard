<?php
namespace Asgard\Core\Tests;

/**
* @runTestsInSeparateProcesses
*/
class AutoloaderTest extends \PHPUnit_Framework_TestCase {
	public function testNamespaceMap() {
		$autoloader = $this->unsetComposer();

		$before = class_exists('Asgard\Core\Tests\Fixtures\Fooo');
		$autoloader->namespaceMap('Asgard\Core\Tests\Fixtures', __DIR__.'/Fixtures/Bar');
		$after = class_exists('Asgard\Core\Tests\Fixtures\Fooo');

		$this->resetComposer();

		$this->assertFalse($before);
		$this->assertTrue($after);
	}

	public function testMap() {
		$autoloader = $this->unsetComposer();

		$before = class_exists('Asgard\Core\Tests\Fixtures\Fooo');
		$autoloader->map('Asgard\Core\Tests\Fixtures\Fooo', __DIR__.'/Fixtures/Bar/Fooo.php');
		$after = class_exists('Asgard\Core\Tests\Fixtures\Fooo');

		$this->resetComposer();

		$this->assertFalse($before);
		$this->assertTrue($after);
	}

	public function testGoUp() {
		$autoloader = $this->unsetComposer();

		include __DIR__.'/Fixtures/GlobalNamespaceFoo.php';

		$before = class_exists('Test\Foo');
		$autoloader->goUp(true); #will search for classes up the namespace, e.g. \Asgard\Core\Foo => \Asgard\Foo
		$after = class_exists('Test\Foo');

		$this->resetComposer();

		$this->assertFalse($before);
		$this->assertTrue($after);
	}

	public function testPsr() {
		$autoloader = $this->unsetComposer();

		$exists = class_exists('Fixtures\Foo');

		$this->resetComposer();

		$this->assertFalse($exists);
	}

	public function testSearch() {
		$autoloader = $this->unsetComposer();

		include __DIR__.'/Fixtures/Bar/Fooo.php';

		$before = class_exists('Fooo');
		$autoloader->search(true); #will search for classes like \...\Foo
		$after = class_exists('Fooo');

		$this->resetComposer();

		$this->assertFalse($before);
		$this->assertTrue($after);
	}

	public function testPreloadFile() {
		$autoloader = $this->unsetComposer();

		$before = class_exists('Foo');
		$autoloader->preload(true);
		$autoloader->preloadFile(__DIR__.'/Fixtures/Foo.php');
		$after = class_exists('Foo');

		$this->resetComposer();

		$this->assertFalse($before);
		$this->assertTrue($after);
	}

	public function testPreloadDir() {
		$autoloader = $this->unsetComposer();

		$before = class_exists('Foo');
		$autoloader->preload(true);
		$autoloader->preloadDir(__DIR__.'/Fixtures/');
		$after = class_exists('Foo');

		$this->resetComposer();

		$this->assertFalse($before);
		$this->assertTrue($after);
	}

	public function unsetComposer() {
		$autoloader = new \Asgard\Core\Autoloader;

		foreach(spl_autoload_functions() as $function) {
			if(is_array($function) && $function[0] instanceof \Composer\Autoload\ClassLoader) {
				$this->composer = $function;
				spl_autoload_unregister($this->composer);
				break;
			}
		}
		spl_autoload_register(array($autoloader, 'autoload'));
		return $autoloader;
	}

	public function resetComposer() {
		foreach(spl_autoload_functions() as $function) {
			if(is_array($function) && $function[0] instanceof \Asgard\Core\Autoloader)
				spl_autoload_unregister($function);
		}
		spl_autoload_register($this->composer);
	}
}