<?php
namespace Asgard\Data\Tests;

class DataTest extends \PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
		\Asgard\Core\App::instance(true)->config->set('bundles', array(
			new \Asgard\Db\Bundle
		))->set('bundlesdirs', array());
		\Asgard\Core\App::loadDefaultApp(false);

		$table = \Asgard\Core\App::get('config')->get('database/prefix').'data';
		try {
			\Asgard\Core\App::get('schema')->drop($table);
		} catch(\Exception $e) {}
		\Asgard\Core\App::get('schema')->create($table, function($table) {	
			$table->add('id', 'int(11)')
				->autoincrement()
				->primary();	
			$table->add('created_at', 'datetime')
				->nullable();
			$table->add('updated_at', 'datetime')
				->nullable();
			$table->add('key', 'varchar(255)')
				->nullable();
			$table->add('value', 'text')
				->nullable();
		});
	}

	public function test1() {
		$data = new \Asgard\Data\Data;

		$this->assertEquals(null, $data->get('foo'));

		$data->set('foo', 123);
		$this->assertEquals(123, $data->get('foo'));

		$data->set('foo', array('a'=>'b', 2));
		$this->assertEquals(array('a'=>'b', 2), $data->get('foo'));

		$data->register(
			'bar',
			function($input) {
				return $input->name;
			},
			function($input) {
				$bar = new Bar;
				$bar->name = $input;
				return $bar;
			}
		);
		$bar = new Bar;
		$bar->name = 'bob';
		$data->set('test', $bar, 'bar');
		$this->assertEquals($bar, $data->get('test'));
	}
}

class Bar {
	public $name;
}