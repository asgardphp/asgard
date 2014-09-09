#ORM

[![Build Status](https://travis-ci.org/asgardphp/orm.svg?branch=master)](https://travis-ci.org/asgardphp/orm)

The ORM package gives you the possibility to store, fetch, search entities and define their relations to each other. It works with the [Asgard Entity package](http://github.com/asgardphp/entity).

##Installation
**If you are using the Asgard Framework you don't need to install this library as it part of the default libraries that Asgard uses.**

In your composer file:

    "require": {
        "asgard/orm": "0.*"
	}

##Entity Relations

To define relations between entities, please read [ORM Relations](http://asgardphp.com/docs/orm-relations).

	<?php
	namespace Blog\Entities\Post;

	class Post extends \Asgard\Entity\Entity {
		public static function definition(\Asgard\Entity\EntityDefinition $definition) {
			$definition->properties = [
				'title', 'content'
			];

			$definition->relations = [
				'tags' => [
					'entity' => 'Blog\Entities\Tag',
					'has'    => 'many'
				],
			];

			$definition->behaviors = [
				new \Asgard\Orm\ORMBehavior
			];
		}
	}

##Persistence

To persist and fetch entities, there are two options:

[Data Mapper](http://asgardphp.com/docs/datamapper)

	$dataMapper->save($entity);

[ORMBehavior (ActiveRecord pattern)](http://asgardphp.com/docs/ormbehavior)

	$entity->save();

##ORM

The ORM class helps you construct queries to manipulate your stored entities. [See the documentation.](http://asgardphp.com/docs/orm-orm)

	$entities = $orm->where('position > ?', 5)->orderBy('title ASC')->get();

##Commands

[List of commands that come with the ORM package.](http://asgardphp.com/docs/orm-commands)

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)