<?php
namespace Asgard\Files\Tests;

class FilesTest extends \PHPUnit_Framework_TestCase {
	protected static $app;

	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');

		$app = new \Asgard\Core\App;
		$app['rulesregistry'] = new \Asgard\Validation\RulesRegistry;
		$app['rulesregistry']->registerNamespace('Asgard\Files\Rules');
		$app['hook'] = new \Asgard\Hook\Hook($app);
		$app['hook']->hook('entity_property_type', function($chain, $type) {
			if($type == 'file')
				return '\Asgard\Files\Libs\FileProperty';
		});
		$app['cache'] = new \Asgard\Cache\NullCache;
		$app['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($app);
		\Asgard\Entity\Entity::setApp($app);
		static::$app = $app;
	}

	public static function tearDownAfteClass() {
		d();
		\Asgard\Utils\FileManager::unlink('web/upload/image.jpg');
	}
	
	public function test1() {
		$news = new Entities\News(array(
			'title' => 'A news',
			'image' => realpath(__dir__.'/files/image.jpg'),
		));
		$this->assertTrue($news->hasFile('image'));
		$this->assertFalse($news->hasFile('somefile'));
		
		$files = Entities\News::fileProperties();
		$this->assertCount(1, $files);
		$this->assertInstanceOf('Asgard\Files\Libs\FileProperty', $files['image']);

		$this->assertCount(0, $news->errors());

		$news->image = realpath(__DIR__.'/files/test.jpg');
		$this->assertEquals('The file image must be an image (jpg, png or gif).', $news->errors()['image']['image']);

		$news->image = realpath(__DIR__.'/files/image.txt');
		$this->assertEquals('The file image must have one of the following extension: jpg, gif, png.', $news->errors()['image']['extension']);

		$news->image = null;
		$this->assertEquals('Image is required.', $news->errors()['image']['required']);

		#save
		$news = new Entities\News(array(
			'title' => 'A news',
			'image' => array(
				'path' => realpath(__DIR__.'/files/image.jpg'),
				'name' => 'image.jpg',
			)
		));
		/*
		$news = new Entities\News(array(
			'title' => 'A news',
			// 'image' => new File(realpath(__dir__.'/files/image.jpg')),
			'image' => new File(array(
				'path' => realpath(__dir__.'/files/image.jpg'),
				'name' => 'test.jpg',
			))
		));
		*/

		$dst = 'web/upload/image.jpg';
		\Asgard\Utils\FileManager::unlink($dst);

		#save
		$news->save();
		$this->assertFileExists($dst, 'Saving image.jpg failed');

		#destroy
		$news->destroy();
		$this->assertFileNotExists($dst, 'Deletion of image.jpg failed');
	}
}