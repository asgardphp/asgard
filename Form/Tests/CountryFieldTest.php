<?php
namespace Asgard\Form\Tests;

class CountryFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->post['field'] = 'SD';
		$form->setRequest($request);
		$form['field'] = new \Asgard\Form\Field\CountryField(['default' => 'AF']);

		$this->assertTrue(strpos((string)($form['field']->def()), '<option value="ZM">Zambia</option>') !== false);
		$this->assertTrue(strpos((string)($form['field']->def()), '<option value="SD" selected="selected">Sudan</option>') !== false);
		$this->assertEquals('SD', $form['field']->value());
	}
}
