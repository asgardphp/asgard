<?php
namespace Asgard\File\Tests;

use Asgard\File\File;

class FileTest extends \PHPUnit_Framework_TestCase {
	public function testFile() {
		$file = new File(__DIR__.'/fixtures/file.txt');
		$file2 = new File(__DIR__.'/fixtures/sdfg.txt');

		$this->assertEquals(3, $file->size());
		$this->assertEquals('text/plain', $file->type());
		$this->assertEquals('txt', $file->extension());
		$this->assertTrue($file->exists());
		$this->assertFalse($file2->exists());
		$this->assertEquals(__DIR__.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'file.txt', $file->src());
	}

	#todo for entity file
	// public function testUrl() {
	// 	$file = new File(__DIR__.'/fixtures/file.txt');
	// 	$file->setWebDir(__DIR__.'/fixtures/');
	// 	$url = new \Asgard\Http\Url(new \Asgard\Http\Request);
	// 	$url->setHost('localhost');
	// 	$url->setRoot('folder');
	// 	$file->setUrl($url);
	// 	$this->assertEquals('http://localhost/folder/file.txt', $file->url());
	// 	$this->assertEquals('http://localhost/folder/file.txt', $file->__toString());
	// }

	public function testCopyRenameAndDelete() {
		\Asgard\File\FileSystem::delete(__DIR__.'/tests/');
		\Asgard\File\FileSystem::delete(__DIR__.'/dir/');

		$file = new File(__DIR__.'/fixtures/file.txt');

		#copy
		$copy = $file->copy(__DIR__.'/tests/copy.txt');
		$this->assertTrue($copy->exists());
		$this->assertEquals('copy.txt', $copy->getName());

		#renme
		$copy->rename(__DIR__.'/tests/new.txt');
		$this->assertFalse(file_exists(__DIR__.'/tests/copy.txt'));
		$this->assertTrue(file_exists(__DIR__.'/tests/new.txt'));

		#moveToDir
		$copy->moveToDir(__DIR__.'/dir/');
		$this->assertFalse(file_exists(__DIR__.'/tests/new.txt'));
		$this->assertTrue(file_exists(__DIR__.'/dir/new.txt'));

		#delete
		$copy->delete();
		$this->assertFalse($copy->exists());
	}
}