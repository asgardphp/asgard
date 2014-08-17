#Database

[![Build Status](https://travis-ci.org/asgardphp/db.svg?branch=master)](https://travis-ci.org/asgardphp/db)

The DB package lets you manipulate the database, build SQL queries and manipulate the tables structure with Schema.

##Installation
**If you are using the Asgard Framework you don't need to install this library as it part of the default libraries that Asgard uses.**

In your composer file:

    "require": {
        "asgard/db": "dev-master"
	}

##DB

To connect to the database and make SQL queries. [See the documentation.](http://asgardphp.com/docs/db-db)

	$rows = $db->query('SELECT * FROM news ORDER BY id DESC')->all();

##DAL

To build SQL queries in a Object-Oriented manner. [See the documentation.](http://asgardphp.com/docs/db-dal)

	$rows = $dal->from('news')->orderBy('id DESC')->all();

##Schema

Build, modify and drop tables. [See the documentation.](http://asgardphp.com/docs/db-schema)

	schema->table('news', function($table) {
		$table->add('id', 'int(11)')
			->autoincrement()
			->primary();	
		$table->add('created_at', 'datetime')
			->nullable();	
		$table->add('updated_at', 'datetime')
			->nullable();	
		$table->add('title', 'varchar(255)')
			->nullable();
	});

##Commands

[List of commands that come with the DB package.](http://asgardphp.com/docs/db-commands)

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)