<?php
class Post extends \Asgard\Migration\DBMigration {
	public function up() {
		$this->container['schema']->create('author', function($table) {
			$table->addColumn('id', 'integer', [
				'notnull' => true,
				'autoincrement' => true,
			]);
			$table->addColumn('name', 'string', [
			]);
			$table->setPrimaryKey(
				[
					'id',
				]
			);
		});
		
		$this->container['schema']->create('category', function($table) {
			$table->addColumn('id', 'integer', [
				'notnull' => true,
				'autoincrement' => true,
			]);
			$table->addColumn('name', 'string', [
			]);
			$table->setPrimaryKey(
				[
					'id',
				]
			);
		});
		
		$this->container['schema']->table('post', function($table) {
			$table->addColumn('content2', 'string', [
			]);
			$table->changeColumn('title', [
				'default' => 'b',
				'notnull' => '',
			]);
			$table->dropColumn('posted');
			$table->addIndex(
				[
					'content2',
				]
			);
			$table->dropIndex('title');
		});
	}

	public function down() {
		$this->container['schema']->drop('author');
		
		$this->container['schema']->drop('category');
		
		$this->container['schema']->table('post', function($table) {
			$table->addColumn('posted', 'date', [
			]);
			$table->changeColumn('title', [
				'default' => 'a',
				'notnull' => '1',
			]);
			$table->dropColumn('content2');
			$table->addUniqueIndex(
				[
					'title',
				]
			);
			$table->dropIndex('content2');
		});
	}
}