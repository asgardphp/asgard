<?php
namespace Coxis\Files\Tests;

class FilesTest extends \PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
		require_once(_CORE_DIR_.'core.php');

		\Coxis\Core\App::instance(true)->config->set('bundles', array(
			_COXIS_DIR_.'core',
			_COXIS_DIR_.'files',
		));
		\Coxis\Core\App::loadDefaultApp();
	}

	public static function tearDownAfteClass() {
		d();
		\Coxis\Utils\FileManager::unlink('web/upload/image.jpg');
	}
	
	public function test1() {
		$news = new Entities\News(array(
			'title' => 'A news',
			'image' => realpath(dirname(__FILE__).'/files/image.jpg'),
		));
		$this->assertTrue($news->hasFile('image'));
		$this->assertFalse($news->hasFile('somefile'));
		
		$files = Entities\News::fileProperties();
		$this->assertCount(1, $files);
		$this->assertInstanceOf('Coxis\Files\Libs\FileProperty', $files['image']);

		$this->assertCount(0, $news->errors());

		$news->image = realpath(dirname(__FILE__).'/files/test.txt');
		$this->assertEquals('The file "image" must be an image.', $news->errors()['image'][0]);

		$news->image = realpath(dirname(__FILE__).'/files/image.txt');
		$this->assertEquals('This type of file is not allowed "txt".', $news->errors()['image'][0]);

		$news->image = null;
		$this->assertEquals('The file "image" does not exist.', $news->errors()['image'][0]);

		#save
		$news = new Entities\News(array(
			'title' => 'A news',
			'image' => array(
				'path' => realpath(dirname(__FILE__).'/files/image.jpg'),
				'name' => 'image.jpg',
			)
		));
		/*
		$news = new Entities\News(array(
			'title' => 'A news',
			// 'image' => new File(realpath(dirname(__FILE__).'/files/image.jpg')),
			'image' => new File(array(
				'path' => realpath(dirname(__FILE__).'/files/image.jpg'),
				'name' => 'test.jpg',
			))
		));
		*/

		$dst = 'web/upload/image.jpg';
		\Coxis\Utils\FileManager::unlink($dst);

		#save
		$news->save();
		$this->assertFileExists($dst, 'Saving image.jpg failed');

		#destroy
		$news->destroy();
		$this->assertFileNotExists($dst, 'Deletion of image.jpg failed');
	}
}