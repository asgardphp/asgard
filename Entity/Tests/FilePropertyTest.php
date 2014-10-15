<?php
namespace Asgard\Entity\Tests;

class FilePropertyTest extends \PHPUnit_Framework_TestCase {
	protected static $container;

	public static function setUpBeforeClass() {
		$container = new \Asgard\Container\Container;
		$container['hooks'] = new \Asgard\Hook\HookManager($container);
		$container['httpKernel'] = new \Asgard\Http\HttpKernel;
		$request = new \Asgard\Http\Request;
		$request->url->setHost('localhost');
		$request->url->setRoot('folder');
		$container['httpKernel']->addRequest($request);
		$container->register('Asgard.Entity.PropertyType.file', function($container, $params) {
			$prop = new \Asgard\Entity\Properties\FileProperty($params);
			$prop->setWebDir(__DIR__.'/Fixtures/');
			$prop->setUrl($container['httpKernel']->getRequest()->url);
			return $prop;
		});

		$entityManager = $container['entityManager'] = new \Asgard\Entity\EntityManager($container);
		$rulesRegistry = new \Asgard\Validation\RulesRegistry;
		$rulesRegistry->registerNamespace('Asgard\File\Rules');
		$entityManager->setValidatorFactory(new \Asgard\Validation\ValidatorFactory($rulesRegistry));
		#set the EntityManager static instance for activerecord-like entities (e.g. new Article or Article::find())
		\Asgard\Entity\EntityManager::setInstance($entityManager);
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
		$definition = $ent::getStaticDefinition();

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