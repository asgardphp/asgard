<?php
class Data {
	public static function up() {
		$table = \Asgard\Core\App::get('config')->get('database/prefix').'data';
		\Asgard\Core\App::get('schema')->create($table, function($table) {	
			$table->add('id', 'int(11)')
				->autoincrement()
				->primary();	
			$table->add('position', 'int(11)')
				->nullable();
			$table->add('created_at', 'datetime')
				->nullable();
			$table->add('updated_at', 'datetime')
				->nullable();
			$table->add('question', 'varchar(255)')
				->nullable();
			$table->add('answer', 'varchar(255)')
				->nullable();
		});
	}
	
	public static function down() {
		\Asgard\Core\App::get('schema')->drop(\Asgard\Core\App::get('config')->get('database/prefix').'data');
	}
}