#Migration

[![Build Status](https://travis-ci.org/asgardphp/migration.svg?branch=master)](https://travis-ci.org/asgardphp/migration)

The migration packages lets you manage and execute your migrations.

- [Installation](#installation)
- [Overview](#overview)
- [MigrationManager](#migrationmanager)
- [Tracker](#tracker)
- [Commands](#commands)

<a name="installation"></a>
##Installation
**If you are working on an Asgard project you don't need to install this library as it is already part of the standard libraries.**

	composer require asgard/migration 0.*

<a name="lifecycle"></a>
##Overview

All migrations must extend \Asgard\Migration\Migration or \Asgard\Migration\DBMigration (which extends \Asgard\Migration\Migration).

\Asgard\Migration\DBMigration can be useful for database migrations as it will automatically begin a transaction before the migration is executed and commit it only if the migration was successful.

Migrations must implements the up() or/and down() method.

For example:

	<?php
	class News extends \Asgard\Migration\DBMigration {
		public function up() {
			$this->schema->create('news', function($table) {
				$table->addColumn('id', 'integer', [
					'length' => 11,
					'autoincrement' => true,
				]);
				$table->addColumn('text', 'string', [
					'length' => '255',
				]);
				$table->addColumn('meta_title', 'text', [
					'length' => '65535',
				]);

				$table->setPrimaryKey(['id']);
			});
		}
		
		public function down() {
			$this->schema->drop('news');
		}
	}

All the migration files must be located in the same folder. In a Asgard project, the migration folder is at migrations/.

This folder contains one json file:

 * migrations.json contains all active migrations

The migration statutes are store in the _migrations table.

Examples:

migrations.json:

	{
	    "Data": {
	        "added": 1401943722.9835
	    }
	}

The Data migration must be in a file called Data.php at migrations/Data.php.

<a name="migrationmanager"></a>
##MigrationManager

###Usage in the Asgard Framework

	$migrationManager = $container['migrationManager'];
	
The [container](docs/container) is often accessible as a method parameter or through a [ContainerAware](docs/container#containeraware) object. You can also use the [singleton](docs/container#usage-outside) but it is not recommended.

###Usage outside the Asgard Framework

	$migrationManager = new \Asgard\Migration\MigrationManager('/path/to/migrations/', $container /*optional*/);

###Methods

Add a migration:

	$migrationManager->add('/path/to/Migration.php');

Will copy the file to the migrations directory and add it to migrations.json.

Check if a migration already exists:

	$migrationManager->has('Migrationname');

Remove a migration:

	$migrationManager->remove('Migrationname');

Execute a migration:

	$migrationManager->migrate('Migrationname');

Migrate a file:

	$migrationManager->migrateFile('/path/to/Migration.php');

Migrate all migrations:

	$migrationManager->migrateAll();

Reset migrations (rollback and re-migrate all migrations):

	$migrationManager->reset();

Unmigrate a specific migration:

	$migrationManager->unmigrate('Migrationname');

Rollback the last migration:

	$migrationManager->rollback();

Rollback up to a specific migration:

	$migrationManager->rollbackUntil('Migrationname');

Create a new migration:

	$up = "$this->schema->create('news', function($table) {
				$table->addColumn('id', 'integer', [
					'length' => 11,
					'autoincrement' => true,
				]);
				$table->addColumn('text', 'string', [
					'length' => '255',
				]);
				$table->addColumn('meta_title', 'text', [
					'length' => '65535',
				]);

				$table->setPrimaryKey(['id']);
			});";
	$down = "$this->schema->drop('news');";
	$migrationManager->create($up, $down, 'Migrationname', $class='\Asgard\Migration\Migration');

<a name="tracker"></a>
##Tracker

The tracker is helpful to track the statuses of your migrations.

###Usage in the Asgard Framework

	$tracker = $container['migrationManager']->getTracker();

###Usage outside the Asgard Framework

	$tracker = $migrationManager->getTracker();
	
The [container](docs/container) is often accessible as a method parameter or through a [ContainerAware](docs/container#containeraware) object. You can also use the [singleton](docs/container#usage-outside) but it is not recommended.

###Methods

Get all migrations:

	$tracker->getList();

Get all non-executed migrations:

	$tracker->getDownList();

Get all executed migrations:

	$tracker->getUpList();

Check if a migration exists:

	$tracker->has('migrationName');

Get the next migration to be executed:

	$tracker->getNext();

Get the last executed migration:

	$tracker->getLast();

Get all migrations up to a specific migration:

	$tracker->getUntil('migrationName');

Get all migrated migrations up to a specific migration in reverse order:

	$tracker->getRevereMigratedUntil('migrationName');

Add a migration to migrations.json (the migration file must already be in the migrations folder):

	$tracker->add('migrationName');

Remove a migration from migrations.json (without deleting the file):

	$tracker->remove('migrationName');

Mark a migration as unmigrated:

	$tracker->unmigrate('migrationName');

Mark a migration as migrated:

	$tracker->migrate('migrationName');

Check if a migration is up:

	$tracker->isUp('migrationName');

<a name="commands"></a>
##Commands

###AddCommand

Add a new migration to the list

Usage:

	php console migrations:add [src]

src: The migration file

###ListCommand

Displays the list of migrations

Usage:

	php console migrations:list

###MigrateCommand

Run the migrations

Usage:

	php console migrate

###MigrateOneCommand

Run a migration

Usage:

	php console migrations:migrate [migration]

migration: The migration name

###RefreshCommand

Reset and re-run all migrations

Usage:

	php console migrations:refresh

###RemoveCommand

Remove a migration

Usage:

	php console migrations:remove [migration]

migration: The migration name

###RollbackCommand

Rollback the last database migration

Usage:

	php console migrations:rollback

###UnmigrateCommand

Unmigrate a migration

Usage:

	php console migrations:unmigrate [migration]

migration: The migration name

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)