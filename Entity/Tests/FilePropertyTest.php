<?php
namespace Asgard\Entity\Tests;

class FilePropertyTest extends \PHPUnit_Framework_TestCase {
	protected static $container;

	public static function setUpBeforeClass() {
		$container = new \Asgard\Container\Container;
		$container['config'] = new \Asgard\Config\Config;
		$container['config']['webdir'] = __DIR__.'/Fixtures/';
		$container['hooks'] = new \Asgard\Hook\HooksManager($container);
		$container['cache'] = new \Asgard\Cache\NullCache;
		$container['rulesregistry'] = \Asgard\Validation\RulesRegistry::getInstance();
		$container['rulesregistry']->registerNamespace('Asgard\File\Rules');
		$container['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($container);
		// $container['kernel'] = new \Asgard\Core\Kernel();
		// $container['kernel']['webdir'] = __DIR__.'/Fixtures/';
		$container['request'] = new \Asgard\Http\Request;
		$container['request']->url->setHost('localhost');
		$container['request']->url->setRoot('folder');
		\Asgard\Entity\Entity::setContainer($container);
		static::$container = $container;
	}

	public function testSet() {
		$ent = new Fixtures\EntityWithFile([
			'name' => 'Entity',
			'files' => [
				__DIR__.'/Fixtures/file1.txt',
				__DIR__.'/Fixtures/file2.txt',
			],
			'file' => __DIR__.'/Fixtures/file.txt',
		]);

		$this->assertInstanceOf('Asgard\File\File', $ent->file);
		$this->assertEquals('http://localhost/folder/file.txt', $ent->file->__toString());

		$this->assertEquals(realpath(__DIR__.'/Fixtures/file1.txt'), $ent->files[0]->src());
		$this->assertEquals(realpath(__DIR__.'/Fixtures/file2.txt'), $ent->files[1]->src());
	}

	public function testSerialize() {
		$ent = new Fixtures\EntityWithFile([
			'name' => 'Entity',
			'files' => [
				__DIR__.'/Fixtures/file1.txt',
				__DIR__.'/Fixtures/file2.txt',
			],
			'file' => __DIR__.'/Fixtures/file.txt',
		]);
		$definition = $ent::getDefinition();

		$this->assertEquals(realpath(__DIR__.'/Fixtures/file.txt'), $definition->property('file')->serialize($ent->file));
		$this->assertEquals(serialize([
				realpath(__DIR__.'/Fixtures/file1.txt'),
				realpath(__DIR__.'/Fixtures/file2.txt'),
		]), $definition->property('files')->serialize($ent->files));
	}

	public function testValidation() {
		$ent = new Fixtures\EntityWithFile([
			'name' => 'Entity',
			'file' => __DIR__.'/Fixtures/file.a',
			'files' => [
				__DIR__.'/Fixtures/file1.txt',
				__DIR__.'/Fixtures/file2.a',
			],
		]);

		$this->assertEquals([
			'files' => [
				1 => 'The file files must have one of the following extension: pdf, doc, jpg, jpeg, png, docx, gif, rtf, ppt, xls, zip, txt.',
			],
			'file' => [
				'extension' => 'The file file must have one of the following extension: pdf, doc, jpg, jpeg, png, docx, gif, rtf, ppt, xls, zip, txt.',
			]
		], $ent->errors());
	}
}