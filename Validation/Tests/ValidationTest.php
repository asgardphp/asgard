<?php
namespace Asgard\Validation\Tests;

use Asgard\Validation\Validator as v;
use Asgard\Validation\RulesRegistry;
use Asgard\Validation\InputBag;

class Test extends \PHPUnit_Framework_TestCase {
	public function test() {
		$v = new v;
		$v->rules(array(
			'min' => 5,
		));
		$this->assertFalse($v->valid(3));
		$this->assertTrue($v->valid(7));

		$this->assertFalse(v::min(5)->valid(3));
		$this->assertTrue(v::min(5)->valid(7));

		$report = v::min(5)->errors(3);
		$this->assertEquals('"3" is not valid.', $report->error());
		$this->assertEquals('"3" must be greater than 5.', $report->first());

		$v = new v;
		$v->attribute('article.score', array(
			'min' => 5
		));
		$this->assertTrue($v->valid(array('article'=>array('score' => 7))));
		$this->assertFalse($v->valid(array('article'=>array('score' => 3))));

		$v = new v;
		$v->attribute('article', 'required');
		$this->assertTrue($v->valid(array('article'=>'a')));
		$this->assertFalse($v->valid(array()));

		$v = new v;
		$v->attribute('article')->min(5);
		$this->assertTrue($v->valid(array('article'=>7)));
		$this->assertTrue(v::attribute('article', v::min(5))->valid(array('article'=>7)));
		$this->assertFalse(v::attribute('article', v::min(5))->valid(array('article'=>3)));

		$this->assertFalse(v::required()->valid(null));
		$this->assertTrue(v::required()->valid(1));

		$this->assertFalse(v::attribute('user.confirm', v::same('password'))->valid(array('user' => array('password'=>123, 'confirm'=>321))));
		$this->assertTrue(v::attribute('user.confirm', v::same('password'))->valid(array('user' => array('password'=>123, 'confirm'=>123))));
		$this->assertFalse(v::attribute('user.confirm', v::same('<.password'))->valid(array('user' => array('confirm'=>123), 'password'=>321)));
		$this->assertTrue(v::attribute('user.confirm', v::same('<.password'))->valid(array('user' => array('confirm'=>123), 'password'=>123)));
		$this->assertEquals('Confirm must be same as password.', v::attribute('confirm', v::same('password'))->errors(array('password'=>123, 'confirm'=>321))->attribute('confirm')->first());

		$this->assertEquals('Score must be greater than 5.', v::attribute('score', v::min(5))->errors(array('score'=>3))->attribute('score')->first());
		$this->assertEquals('Score must be greater than 5.', v::attribute('score', v::min(5))->errors(array('score'=>3))->attribute('score')->error('min'));

		v::min(5)->assert(7);
		try {
			v::min(5)->assert(3);
		} catch(\Asgard\Validation\ValidatorException $e) {
			$this->assertEquals('"3" must be greater than 5.', $e->errors()->first());
		}

		$this->assertFalse(v::callback(function($input) { return $input >= 5; })->valid(3));
		$this->assertTrue(v::callback(function($input) { return $input >= 5; })->valid(7));
		$this->assertEquals('"3" is invalid.', v::callback(function($input) { return $input >= 5; })->errors(3)->first());

		$v = new v;
		$this->assertFalse($v->rule(function($input) { return $input >= 5; })->valid(3));
		$v = new v;
		$this->assertTrue($v->rule(function($input) { return $input >= 5; })->valid(7));

		$report = v::attributes(array('title'=>array('min'=>5), 'content'=>v::min(5)))->errors(array('title'=>2, 'content'=>1));
		$first = array();
		foreach($report->attributes() as $attribute=>$r)
			$first[$attribute] = $r->first();
		$this->assertEquals(array('title'=>'Title must be greater than 5.', 'content'=>'Content must be greater than 5.'), $first);

		$this->assertEquals('Array is not valid.', v::int(5)->errors(array())->error());
		$this->assertEquals('Object is not valid.', v::int(5)->errors(new \stdClass)->error());
		$this->assertEquals('"a" is not valid.', v::int(5)->errors('a')->error());
		$this->assertEquals('Score is not valid.', v::attribute('score', v::int(5))->errors(array('score'=>'a'))->error('score'));

		$this->assertTrue(v::min(5)->errors(3)->hasError());
		$this->assertFalse(v::min(5)->errors(7)->hasError());
		
		$this->assertEquals(array('title', 'content'), v::attributes(array('title'=>v::min(5), 'content'=>v::min(5)))->errors(array('title'=>3, 'content'=>3))->failed());

		$this->assertEquals('"3" is not valid.', (string)v::min(5)->errors(3));

		$this->assertTrue(v::equal(1)->valid(1));
		$this->assertFalse(v::equal(1)->valid(2));
		$this->assertEquals('"aa" must be equal to 1.', v::equal(1)->errors('aa')->first());

		$this->assertTrue(v::length(2)->valid('aa'));
		$this->assertFalse(v::length(2)->valid('aaa'));
		$this->assertEquals('"aaa" must be 2 characters long.', v::length(2)->errors('aaa')->first());

		$this->assertTrue(v::lengthBetween(2, 4)->valid('aaa'));
		$this->assertFalse(v::lengthBetween(2, 4)->valid('aaaaaa'));
		$this->assertEquals('"a" must be between 2 and 4 characters long.', v::lengthBetween(2, 4)->errors('a')->first());
		$this->assertEquals('"a" must be more than 2 characters long.', v::lengthBetween(2, null)->errors('a')->first());
		$this->assertEquals('"aaaaa" must be less than 4 characters long.', v::lengthBetween(null, 4)->errors('aaaaa')->first());

		$this->assertTrue(v::email()->valid('a@a.com'));
		$this->assertFalse(v::email()->valid('adsfg'));
		$this->assertEquals('"aa" must be a valid email address.', v::email()->errors('aa')->first());

		$this->assertTrue(v::date()->valid('1988-09-20'));
		$this->assertFalse(v::date()->valid('asasdf'));
		$this->assertEquals('"aa" must be a date (yyyy-mm-dd).', v::date()->errors('aa')->first());

		$this->assertTrue(v::regex('/[0-9]+/')->valid('045086'));
		$this->assertFalse(v::regex('/[0-9]+/')->valid('asdfgh'));
		$this->assertEquals('"aa" must match pattern "/[0-9]+/".', v::regex('/[0-9]+/')->errors('aa')->first());

		$this->assertTrue(v::any(v::min(5), v::equal(3))->valid(3));
		$this->assertFalse(v::any(v::min(5), v::equal(3))->valid(2));
		$this->assertEquals('"2" is invalid.', v::any(v::min(5), v::equal(3))->errors(2)->first());

		#IsNull
		$this->assertFalse(v::min(1)->valid(0));
		$this->assertTrue(v::isNull(function($i) { return $i==0; })->min(1)->valid(0));
		$this->assertTrue(v::isNull(function($i) { return $i==0; })->valid(0));
		$this->assertFalse(v::isNull(function($i) { return $i==0; })->required()->valid(0));

		#Rule all
		$this->assertTrue(v::all(v::min(5), v::equal(6))->valid(6));
		$this->assertFalse(v::all(v::min(5), v::equal(6))->valid(7));
		$this->assertEquals('"7" is invalid.', v::all(v::min(5), v::equal(6))->errors(7)->first());

		#Rule each
		$this->assertTrue(v::each(v::min(5))->valid(array(5,6,7,8,9)));
		$this->assertFalse(v::each(v::min(5))->valid(array(1,2,3,4,5,6,7,8,9)));

		#handle each
		$this->assertFalse(v::ruleEach('min', 5)->valid(array(1,2,3,4,5,6,7,8,9)));
		$this->assertTrue(v::rule('min', 5)->valid(array(1,2,3,4,5,6,7,8,9)));

		RulesRegistry::getInstance()->messages(array('min'=>':attribute shall be greater than :min!'));
		$this->assertEquals('"3" shall be greater than 5!', v::min(5)->errors(3)->first());

		RulesRegistry::getInstance()->register('test', function($input){ return $input == 'a'; });
		$this->assertTrue(v::test()->valid('a'));
		$this->assertFalse(v::test()->valid('b'));

		RulesRegistry::getInstance()->registerNamespace('Asgard\Validation\Tests');
		$this->assertTrue(v::ble('a')->valid('a'));
		$this->assertFalse(v::ble('a')->valid('b'));

		RulesRegistry::getInstance()->register('greaterThan', function($input, $parent, $v){ $v->min($parent->input('min')); });
		$this->assertFalse(v::attribute('title', v::greaterThan(5))->valid(array('title'=>'4', 'min'=>5)));
		$this->assertTrue(v::attribute('title', v::greaterThan(5))->valid(array('title'=>'4', 'min'=>4)));

		$this->assertFalse(v::attribute('payment', array('required'=>true))->valid(array('amount'=>500)));
		$this->assertFalse(v::attribute('payment', array('required'=>function($input, $parent, $v) {
			if($parent->attribute('amount')->input() >= 400)
				return true;
		}))->valid(array('amount'=>500)));
		$this->assertTrue(v::attribute('payment', array('required'=>function($input, $parent, $v) {
			if($parent->attribute('amount')->input() >= 400)
				return true;
		}))->valid(array('amount'=>300)));
		$this->assertTrue(v::attribute('payment', array('required'=>function($input, $parent, $v) {
			if($parent->attribute('amount')->input() >= 400)
				return true;
		}))->valid(array('amount'=>500, 'payment'=>10)));

		#Report
		$this->assertEquals(array(
			'min' => '"2" shall be greater than 5!',
			'equal' => '"2" must be equal to 6.',
		), v::min(5)->equal(6)->errors(2)->errors());

		$this->assertEquals('Score is not valid.', v::attribute('score', v::min(5))->errors(array('score'=>3))->first());

		#Setting a subreport
		$report = (new v)->errors(array('users'=>array()));
		$report->attribute('users.user1', v::min(5)->errors(3));
		$this->assertEquals('"3" shall be greater than 5!', $report->first('users.user1'));

		#Translation
		$translator = new Translator;
		$this->assertEquals('"5" doit être égal à 6.', v::equal(6)->setTranslator($translator)->errors(5)->first());

		#Input
		$v = new v;
		$v->attribute('category.title', v::lengthBetween(10, null))->valid(array('category'=>array('title'=>'blabla')));
		$this->assertEquals('blabla', $v->getInput()->attribute('category.title')->input());
		$this->assertEquals('blabla', $v->getInput()->attributes()['category']->attributes()['title']->input());

		#hasAttribute
		$input = new InputBag(array('title'=>123));
		$this->assertTrue($input->hasAttribute('title'));
		$this->assertFalse($input->hasAttribute('content'));

		#default message
		$v = v::min(5)->setDefaultMessage('title', 'Not valid.');
		$this->assertEquals('Not valid.', $v->attribute('title')->getDefaultMessage());

		#rule messages
		$v = new v;
		$this->assertEquals('Must be over min!', $v->ruleMessages(array(
			'min' => 'Must be over min!'
		))->getRuleMessage('min'));

		#a supprimer? l.151/152 de Validator
		$this->assertFalse(v::rule(new \Asgard\Validation\Rules\Min(5))->valid(4));

		#custom registry
		$registry = new RulesRegistry;
		$registry->register('bar', function($input){ return false; });
		$v = new v;
		$this->assertFalse($v->setRegistry($registry)->bar()->valid(1));

		#2 times the same rule
		$this->assertFalse(v::contains('a')->contains('b')->valid('ac'));
		$this->assertFalse(v::contains('a')->contains('b')->valid('bc'));
		$this->assertTrue(v::contains('a')->contains('b')->valid('abc'));
		$this->assertEquals(array(
			'contains' => '"c" must contain "a".',
			'contains-1' => '"c" must contain "b".',
		), v::contains('a')->contains('b')->errors('c')->errors());

		#2 times the same rule in an attribute validator
		$this->assertEquals(array(
			'contains' => '"c" must contain "a".',
			'contains-1' => '"c" must contain "b".',
		), v::contains('a')->rule(v::contains('b'))->errors('c')->errors());

		#merging attributes validators
		$this->assertEquals(array(
			'contains' => 'Title must contain "a".',
			'contains-1' => 'Title must contain "b".',
		), v::rule(v::attribute('title', v::contains('a')))->attribute('title', v::contains('b'))->errors(array('title'=>'c'))->attribute('title')->errors());

		#rule not found
		try {
			v::qwqwqw();
			$this->assertFalse(true);
		} catch(\Exception $e) {
			$this->assertTrue(true);
		}
	}
}