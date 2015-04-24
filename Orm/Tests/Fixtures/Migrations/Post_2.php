<?php
class Post_123 extends \Asgard\Migration\DBMigration {
	public function up() {
		$this->schema->create('author', function($table) {
			$table->addColumn('id', 'integer', [
				'notnull' => true,
				'autoincrement' => true,
			]);
			$table->addColumn('name', 'string', [
				'length' => 255,
			]);
			$table->setPrimaryKey(
				[
					'id',
				]
			);
		});
		
		$this->schema->create('category', function($table) {
			$table->addColumn('id', 'integer', [
				'notnull' => true,
				'autoincrement' => true,
			]);
			$table->addColumn('name', 'string', [
				'length' => 255,
			]);
			$table->setPrimaryKey(
				[
					'id',
				]
			);
		});
		
		$this->schema->table('post', function($table) {
			$table->addColumn('content2', 'string', [
				'length' => 255,
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
		$this->schema->drop('author');
		
		$this->schema->drop('category');
		
		$this->schema->table('post', function($table) {
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