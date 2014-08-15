<?php
namespace Asgard\Form\Tests;

class DayFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->post['field'] = '5';
		$form->setRequest($request);
		$form['field'] = new \Asgard\Form\Fields\DayField;

		$this->assertTrue(strpos((string)($form['field']->def()), '<option value="4">4</option>') !== false);
		$this->assertTrue(strpos((string)($form['field']->def()), '<option value="5" selected="selected">5</option>') !== false);
		$this->assertEquals('5', $form['field']->value());
	}
}
