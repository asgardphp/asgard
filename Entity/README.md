#Entities

[![Build Status](https://travis-ci.org/asgardphp/entity.svg?branch=master)](https://travis-ci.org/asgardphp/entity)

Entities are elements that represent your data. Some frameworks call them models, but they are similar, although entities don't deal themselves directly with the database. They should always be stored in the Entities folder of a bundle.

- [Installation](#installation)
- [Instance](#instance)
- [Definition](#definition)
- [Properties](#properties)
- [Multiple values per property](#multiple)
- [Property hooks](#hooks)
- [Validation](#validation)	
- [Behaviors](#behaviors)
- [I18N](#i18n)
- [Serialization](#serialization)
- [Old/New](#oldnew)

<a name="installation"></a>
##Installation
**If you are working on an Asgard project you don't need to install this library as it is already part of the standard libraries.**

	composer require asgard/entity 0.*

<a name="instance"></a>
##Instance

An entity is instantiated like:

	$article = new Article;

You can also pass default values:

	$article = new Article(['title'=>'hello!']);

<a name="definition"></a>
##Definition

An entity is defined by a class:

	<?php
	class Article extends \Asgard\Entity\Entity {
		public static function definition($definition) {
			$definition->properties = [
				'title' => [
					'required'=>true
				],
				'content' => 'text',
				'posted_on' => 'date'
			]
		}
	}

Here we have defined the entity Article, with 3 properties: title, content and posted\_on. title has the default type "string" (text with less than 255 characters), content has the type "text", while posted\_on is a date.
There are actually 4 different ways to define a property:

	$definition->properties = [
		'title'
	]
	#or
	$definition->properties = [
		'title' => 'string'
	]
	#or
	$definition->properties = [
		'title' => [
			'type' => 'string',
			...
		]
	]
	#or
	$definition->properties = [
		'title' => new \Asgard\Entity\Property\StringProperty([..])
	]

The type is optional and defaults to "string". At the moment you can use the types: "boolean", "date", "datetime", "double", "email", "file", "image", "integer", "text" and "string".

Each type affects the property in the way the data are stored in the entity, validated or even persisted in the database.

You can access an entity definition through:

	$entity->getDefinition()

The entity properties are accessed like this:

	$article->title
	#or
	$article->get('title')

and editable like this:

	$article->title = 'hello';
	$article->set('title', 'hello')

or set multiple properties at once:

	$article->set(['title'=>'hello', 'content'=>'everyone'])

<a name="properties"></a>
##Property types
**boolean**

	'property_name' => 'boolean'

The property only returns true|false.

**date**

	'property_name' => 'date'

The property returns a [\Carbon\Carbon](https://github.com/briannesbitt/Carbon) object but only shows date when converted to string.

**datetime**

	'property_name' => 'datetime

The property returns a [\Carbon\Carbon](https://github.com/briannesbitt/Carbon) object.

**double**

	'property_name' => 'double'

The property returns a double number.

**email**

	'property_name' => 'email'

The property returns an email address and is only valid if a valid email address was provided.
pos
**file**

	'property_name' => [
		'type' => 'file',
		'web'  => true
	]

The property returns a \Asgard\File\File object.

The web parameter must be set to true if you want to store the file as a web asset.

**integer**

	'property_name' => 'integer'

The property returns an integer number.

**text**

	'property_name' => 'text'

The property returns text with more than 255 characters.

**string**

	'property_name'

The property returns text with less than 255 characters.

<a name="multiple"></a>
##Multiple values per property
A property can even have multiple values (array).
For that, add the multiple parameter like so:

	$definition->properties = [
		'title' => [
			'many' => true
		]
	]

Now, $article->title will return a Asgard\Entity\ManyCollection object which can be used like an array:

	$article->title[] = 'new title';
	$article->title[0] //new title

<a name="hooks"></a>
##Property hooks

You can set a hook for every time you update a property value:

	$definition->properties = [
	    'title' => [
	        'hooks' => [
	            'set' => function($value, $entity) {
					if($value < 10)
						$value = 10;
					return $value;
				}
	        ]
	    ]
	]

The return result will be used as the new property value.

<a name="validation"></a>
##Validation
Properties can have all kinds of parameters, including validation rules. For example:

	$definition->properties = [
		'title' => [
			'validation' => [
				'maxlength' => 10,
				'minlength' => 5
			]
		]
	]

To validate your entity use:

	$article->valid() #returns true of valid, otherwise false
	$article->errors() #returns an array of errors

**Validation groups**

	$definition->properties = [
		'title' => [
			'validation' => [
				'maxlength' => 10,
				'minlength' => [
					5,
					'groups' => ['registration']
				]
			]
		]
	]

With a title with less than 5 characters:

	$article->valid(['registration']); #false
	$article->valid(); #true

For more information on validation, see to the [validation section](docs/validation).

<a name="behaviors"></a>
##Behaviors
Entities can be enhanced by behaviors. Behaviors can add methods and properties, and modify their current behaviors with Hooks.
For example, to make entities sortable, add the following code to the definition method:

	$definition->behaviors = [
		new \Asgard\Behaviors\SortableBehavior()
	];

This adds a property "position" for the Article entity, and two methods: moveAfter($entity) and moveBefore($entity).

You can also pass parameters to behaviors:

	$definition->behaviors = [
		new \Asgard\Behaviors\SortableBehavior('category_id')
	];

If you want to sort articles only between the ones that have the same category\_id.

Please note that this behavior will only work properly if used with the ORMBehavior.

<a name="i18n"></a>
##I18N
Entities handle internationalization by default. Simply add the parameter i18n to a property:

	$definition->properties = [
		'title' => [
			'i18n' => true
		]
	]

From now on, title will have a different version for all the languages that are in the configuration file.

To get the default language:

	$article->title

To get the value in a specific language:

	$article->get('title', 'fr')

To get the values in all the available languages:

	$article->get('title', 'all')

And to set values:

	$article->title = 'hello'
	$article->set('title', 'bonjour', 'fr')
	$article->set(['title'=>'bonjour', 'content'=>'tout le monde'], 'fr')

To know if an entity has i18n properties, use:

	$entityDefinition->is18N()
	#or
	Article::isI18N()

To change an entity instance's default language:

	$entity->setLocale('fr');

Get all locales of an entity:

	$entity->getLocales();

Translate an entity into another locale:

	$frEntity = $enEntity->translate('fr');
	#$enEntity->title = 'Hello'
	#$frEntity->title = 'Bonjour'

**Validation**

	$entity->validI18N(['fr', 'en'], $validationGroups=[]);

Validates the entity and the translations of the given locales. If no locales are given, all the entity locales are used by default.

To get the errors:

	$entity->errorsI18N(['fr', 'en'], $validationGroups=[]);

<a name="serialization"></a>
##Serialization

Entities can be serialized into arrays or json.

To create a serializer:

	$serializer = new \Asgard\Entity\Serializer;
	#or

If you use the default serializer, calling methods directly from the Entity class has the same effect. For example:

	$entity->toArrayRaw($depth=0);

is the same as:

	$serializer->toArrayRaw($entity, $depth=0);

**toArrayRaw**

	$serializer->toArrayRaw($entity, $depth=0);

$depth defines how many levels of relationships to include in the serialization

toArrayRaw will return an array containing the values of all properties.

**toArray**

	$serializer->toArray($entity, $depth=0);

The difference with toArrayRaw, is that toArray will convert all properties into strings and arrays. Including related entities.

**toJSON**

	$serializer->toJSON($entity, $depth=0);

This will return a JSON version of toArray.

**toArrayRawI18N**

	$serializer->toArrayRawI18N($entity, $locales=[], $depth=0);

Same as toArrayRaw, but includes the translations as well.

**toArrayI18N**

	$serializer->toArrayI18N($entity, $locales=[], $depth=0);

Same as toArrayRaw, but includes the translations as well.

**toJSONI18N**

	$serializer->toArrayI18N($entity, $locales=[], $depth=0);

Same as toJSON, but includes the translations as well.

**arrayToJSONI18N**

	$serializer->arrayToJSONI18N($entities, $locales=[], $depth=0);

Calls toJSONI18N on an array of entities.

**arrayToJSON**

	$serializer->arrayToJSON($entities, $locales=[], $depth=0);

Calls toJSON on an array of entities.

<a name="oldnew"></a>
##Old/New

To verify is an entity is old (persisted), use:

	$entity->isOld();
	#or
	$entity->isNew();

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)