<?php
class Post extends \Asgard\Migration\DBMigration {
	public function up() {
		$this->container['schema']->create('post_translation', function($table) {
			$table->add('id', 'int(11)');
			$table->add('locale', 'varchar(50)');
			$table->add('content', 'text')
				->nullable();
		});
		
		$this->container['schema']->create('category_post', function($table) {
			$table->add('post_id', 'int(11)')
				->nullable();
			$table->add('category_id', 'int(11)')
				->nullable();
		});
		
		$this->container['schema']->create('post', function($table) {
			$table->add('id', 'int(11)')
				->primary()
				->autoincrement();
			$table->add('title', 'varchar(255)')
				->unique()
				->def('a');
			$table->add('posted', 'date')
				->nullable();
			$table->add('author_id', 'int(11)')
				->nullable();
		});
		
		$this->container['schema']->create('author', function($table) {
			$table->add('id', 'int(11)')
				->primary()
				->autoincrement();
			$table->add('name', 'varchar(255)')
				->nullable();
		});
		
		$this->container['schema']->create('category', function($table) {
			$table->add('id', 'int(11)')
				->primary()
				->autoincrement();
			$table->add('name', 'varchar(255)')
				->nullable();
		});
	}

	public function down() {
		$this->container['schema']->drop('post_translation');
		
		$this->container['schema']->drop('category_post');
		
		$this->container['schema']->drop('post');
		
		$this->container['schema']->drop('author');
		
		$this->container['schema']->drop('category');
	}
}