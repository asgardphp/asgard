<?php
namespace Asgard\Form\Tests;

class HiddenFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->post['field'] = 'test';
		$form->setRequest($request);
		$form['field'] = new \Asgard\Form\Fields\HiddenField;

		$this->assertEquals('<input type="hidden" name="field" value="test" id="field">', (string)($form['field']->def()));
		$this->assertEquals('test', $form['field']->value());
	}
}
