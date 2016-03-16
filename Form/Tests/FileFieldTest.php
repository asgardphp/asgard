<?php
namespace Asgard\Form\Tests;

class FileFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->file['field'] = new \Asgard\Http\HttpFile('/path/to/file.jpg', 'file.jpg', 'image/jpg', 3000, 0);
		$form->setRequest($request);
		$form['field'] = new \Asgard\Form\Field\FileField;

		$this->assertEquals('<input type="file" name="field" id="field">', (string)($form['field']->def()));
		$this->assertInstanceOf('Asgard\Http\HttpFile', $form['field']->value());
	}
}
