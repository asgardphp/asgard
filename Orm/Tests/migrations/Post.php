<?php
class Post extends \Asgard\Migration\Migration {
	public function up() {
		$this->app['schema']->create('post_translation', function($table) {	
			$table->add('id', 'int(11)');	
			$table->add('locale', 'varchar(50)');	
			$table->add('content', 'text')
				->nullable();
		});
		
		$this->app['schema']->create('category_post', function($table) {	
			$table->add('post_id', 'int(11)');	
			$table->add('category_id', 'int(11)');
		});
		
		$this->app['schema']->create('post', function($table) {	
			$table->add('id', 'int(11)')
				->autoincrement()
				->primary();	
			$table->add('title', 'varchar(255)')
				->def('a')
				->unique();	
			$table->add('posted', 'date')
				->nullable();	
			$table->add('author_id', 'int(11)')
				->nullable();
		});
		
		$this->app['schema']->create('author', function($table) {	
			$table->add('id', 'int(11)')
				->autoincrement()
				->primary();	
			$table->add('name', 'varchar(255)')
				->nullable();
		});
		
		$this->app['schema']->create('category', function($table) {	
			$table->add('id', 'int(11)')
				->autoincrement()
				->primary();	
			$table->add('name', 'varchar(255)')
				->nullable();
		});
	}
	
	public function down() {
		$this->app['schema']->drop('post_translation');
		
		$this->app['schema']->drop('category_post');
		
		$this->app['schema']->drop('post');
		
		$this->app['schema']->drop('author');
		
		$this->app['schema']->drop('category');
	}
}