#ORM

[![Build Status](https://travis-ci.org/asgardphp/orm.svg?branch=master)](https://travis-ci.org/asgardphp/orm)

The ORM package gives you the possibility to store, fetch, search entities and define their relations to each other. It works with the [Asgard Entity package](http://github.com/asgardphp/entity).

##Installation
**If you are working on an Asgard project you don't need to install this library as it is already part of the standard libraries.**

	composer require asgard/orm 0.*

##Entity Relations

To define relations between entities, please read [ORM Relations](docs/orm-relations).

	<?php
	namespace Blog\Entities\Post;

	class Post extends \Asgard\Entity\Entity {
		public static function definition(\Asgard\Entity\Definition $definition) {
			$definition->properties = [
				'title',
				'content',
				'tags' => [
					'type' => 'entity',
					'entity' => 'Blog\Entities\Tag',
					'many'    => true
				],
			];

			$definition->behaviors = [
				new \Asgard\Orm\ORMBehavior
			];
		}
	}

##Persistence

To persist and fetch entities, there are two options:

[Data Mapper](docs/datamapper)

	$dataMapper->save($entity);

[ORMBehavior (ActiveRecord pattern)](docs/ormbehavior)

	$entity->save();

##ORM

The ORM class helps you construct queries to manipulate your stored entities. [See the documentation.](docs/orm-orm)

	$entities = $orm->where('position > ?', 5)->orderBy('title ASC')->get();

##Commands

[List of commands that come with the ORM package.](docs/orm-commands)

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)