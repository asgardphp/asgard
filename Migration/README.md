#Migration

[![Build Status](https://travis-ci.org/asgardphp/migration.svg?branch=master)](https://travis-ci.org/asgardphp/migration)

The migration packages lets you manage and execute your migrations.

- [Installation](#installation)
- [Overview](#overview)
- [MigrationsManager](#migrationsmanager)
- [Tracker](#tracker)
- [Commands](#commands)

<a name="installation"></a>
##Installation
**If you are using the Asgard Framework you don't need to install this library as it part of the default libraries that Asgard uses.**

In your composer file:

    "require": {
        "asgard/migration": "0.*"
	}

<a name="lifecycle"></a>
##Overview

All migrations must extend \Asgard\Migration\Migration or \Asgard\Migration\DBMigration (which extends \Asgard\Migration\Migration).

\Asgard\Migration\DBMigration can be useful for database migrations as it will automatically begin a transaction before the migration is executed and commit it only if the migration was successful.

Migrations must implements the up() or/and down() method.

For example:

	<?php
	class News extends \Asgard\Migration\DBMigration {
		public function up() {
			$this->container['schema']->create('news', function($table) {
				$table->add('id', 'int(11)')
					->autoincrement()
					->primary();	
				$table->add('text', 'varchar(255)');
				$table->add('content', 'text');
			});
		}
		
		public function down() {
			$this->container['schema']->drop('news');
		}
	}

All the migration files must be located in the same folder. In a Asgard project, the migration folder is at migrations/.

This folder contains two json files:

 * migrations.json contains all active migrations
 * tracking.json contains all active migration statuses

You should always use the same migrations.json file across multiple environments, although you can use different tracking.json files to track migrations statuses.

Examples:

migrations.json:

	{
	    "Data": {
	        "added": 1401943722.9835
	    }
	}

tracking.json:

	{
	    "Data": {
	        "migrated": 1401983261.4257
	    }
	}

The Data migration must be in a file called Data.php at migrations/Data.php.

<a name="lifecycle"></a>
##MigrationsManager

###Usage in the Asgard Framework

	$migrationsManager = $container['migrationsManager'];
	
The [container](http://asgardphp.com/docs/container) is often accessible as a parameter or through a [ContainerAware](http://asgardphp.com/docs/container#containeraware) object. You can also use the [singleton](http://asgardphp.com/docs/container#usage-outside) but it is not recommended.

###Usage outside the Asgard Framework

	$migrationsManager = new \Asgard\Migration\MigrationsManager('/path/to/migrations/', $container /*optional*/);

###Methods

Add a migration:

	$migrationsManager->add('/path/to/Migration.php');

Will copy the file to the migrations directory and add it to migrations.json.

Check if a migration already exists:

	$migrationsManager->has('Migrationname');

Remove a migration:

	$migrationsManager->remove('Migrationname');

Execute a migration:

	$migrationsManager->migrate('Migrationname', $tracking=false); #set $tracking to true to update tracking.json

Migrate a file:

	$migrationsManager->migrateFile('/path/to/Migration.php'); #this will not be tracked in tracking.json. Useful for tests.

Migrate all migrations:

	$migrationsManager->migrateAll($tracking=false); #set $tracking to true to update tracking.json

Reset migrations (rollback and re-migrate all migrations):

	$migrationsManager->reset();

Unmigrate a specific migration:

	$migrationsManager->unmigrate('Migrationname');

Rollback the last migration:

	$migrationsManager->rollback();

Rollback up to a specific migration:

	$migrationsManager->rollbackUntil('Migrationname');

Create a new migration:

	$up = "$this->container['schema']->create('news', function($table) {
				$table->add('id', 'int(11)')
					->autoincrement()
					->primary();	
				$table->add('text', 'varchar(255)');
				$table->add('content', 'text');
			});";
	$down = "$this->container['schema']->drop('news');";
	$migrationsManager->create($up, $down, 'Migrationname', $class='\Asgard\Migration\Migration');

<a name="tracker"></a>
##Tracker

The tracker is helpful to track the statuses of your migrations.

###Usage in the Asgard Framework

	$tracker = $container['migrationsManager']->getTracker();

###Usage outside the Asgard Framework

	$tracker = $migrationsManager->getTracker();
	
The [container](http://asgardphp.com/docs/container) is often accessible as a parameter or through a [ContainerAware](http://asgardphp.com/docs/container#containeraware) object. You can also use the [singleton](http://asgardphp.com/docs/container#usage-outside) but it is not recommended.

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

Add a migration to migrations.json (the migration file must already be in the migrations folder):

	$tracker->add('migrationName');

Remove a migration from migrations.json (without deleting the file):

	$tracker->remove('migrationName');

Unmigrate a migration in tracking.json (this will only update the status, not execute the migration):

	$tracker->unmigrate('migrationName');

Migrate a migration in tracking.json (this will only update the status, not execute the migration):

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

src: The migration file

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