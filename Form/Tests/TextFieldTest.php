<?php
namespace Asgard\Form\Tests;

class TextFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->post['field'] = 'test';
		$form->setRequest($request);
		$form['field'] = new \Asgard\Form\Field\TextField(['default' => 'default']);

		$this->assertEquals('<input type="text" name="field" value="test" id="field">', (string)($form['field']->def()));
		$this->assertEquals('<input type="password" name="field" value="test" id="field">', (string)($form['field']->password()));
		$this->assertEquals('test', $form['field']->value());
	}
}
