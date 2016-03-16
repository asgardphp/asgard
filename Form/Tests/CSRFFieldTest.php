<?php
namespace Asgard\Form\Tests;

class CSRFFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		\Asgard\Container\Container::singleton()['session'] = new \Asgard\Common\Bag;

		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->post['field'] = 'test';
		$form->setRequest($request);
		$form['field'] = new \Asgard\Form\Field\CSRFField(['default' => 'default']);

		$this->assertRegExp('/<input type="hidden" name="field" value="(.*?)" id="field">/', (string)($form['field']->def()));
		$this->assertEquals('test', $form['field']->value());

		$this->assertEquals(['field'=>['callback'=>'CSRF token is invalid.']], $form->errors()->errors());
	}
}
