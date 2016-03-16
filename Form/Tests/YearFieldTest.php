<?php
namespace Asgard\Form\Tests;

class YearFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->post['field'] = '2010';
		$form->setRequest($request);
		$form['field'] = new \Asgard\Form\Field\YearField;

		$this->assertTrue(strpos((string)($form['field']->def()), '<option value="2009">2009</option>') !== false);
		$this->assertTrue(strpos((string)($form['field']->def()), '<option value="2010" selected="selected">2010</option>') !== false);
		$this->assertEquals('2010', $form['field']->value());
	}
}
