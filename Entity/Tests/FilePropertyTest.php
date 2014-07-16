<?php
namespace Asgard\Entity\Tests;

class FilePropertyTest extends \PHPUnit_Framework_TestCase {
	protected static $app;

	public static function setUpBeforeClass() {
		$app = new \Asgard\Container\Container;
		$app['config'] = new \Asgard\Config\Config;
		$app['config']['webdir'] = __DIR__.'/Fixtures/';
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$app['cache'] = new \Asgard\Cache\NullCache;
		$app['rulesregistry'] = \Asgard\Validation\RulesRegistry::getInstance();
		$app['rulesregistry']->registerNamespace('Asgard\File\Rules');
		$app['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($app);
		// $app['kernel'] = new \Asgard\Core\Kernel();
		// $app['kernel']['webdir'] = __DIR__.'/Fixtures/';
		$app['request'] = new \Asgard\Http\Request;
		$app['request']->url->setHost('localhost');
		$app['request']->url->setRoot('folder');
		\Asgard\Entity\Entity::setApp($app);
		static::$app = $app;
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