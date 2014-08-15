<?php
namespace Asgard\Form\Tests;

class CSRFFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->post['field'] = 'test';
		$form->setRequest($request);
		$form['field'] = new \Asgard\Form\Fields\CSRFField(['default' => 'default']);

		$this->assertRegExp('/<input type="hidden" name="field" value="(.*?)" id="field">/', (string)($form['field']->def()));
		$this->assertEquals('test', $form['field']->value());

		$this->assertEquals(['field'=>['callback'=>'CSRF token is invalid.']], $form->errors());
	}
}
