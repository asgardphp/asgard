#EntityForm

[![Build Status](https://travis-ci.org/asgardphp/entityform.svg?branch=master)](https://travis-ci.org/asgardphp/entityform)

Entityform help you generate forms from entities. It creates the form fields corresponding to all the entity properties. Entityform is a sub-class of [Form](docs/form).

- [Installation](#installation)
- [Usage in the Asgard Framework](#usage-asgard)
- [Usage outside the Asgard Framework](#usage-outside)
- [Add entity relations](#relations)
- [Save the entity](#save)
- [Get the entity](#get)
- [EntityFieldSolver](#solver)

<a name="installation"></a>
##Installation
**If you are working on an Asgard project you don't need to install this library as it is already part of the standard libraries.**

	composer require asgard/entityform 0.*

<a name="usage-asgard"></a>
##Usage in the Asgard Framework
The advantage of using the form service is that it will provides the Form with all the necessary dependencies:

	$request = \Asgard\Http\Request::CreateFromGlobals();
	$entity  = new Article;
	$container->make('entityForm', [
		$entity, #required
		[ #optional
			'action'  => 'form.php',
			'enctype' => 'multipart/form-data',
			'attrs'   => [
				'class' => 'formClass'
			]
		],
		$request, #optional, Asgard can provide the form with the current request
	]);
	
The [container](docs/container) is often accessible as a method parameter or through a [ContainerAware](docs/container#containeraware) object. You can also use the [singleton](docs/container#usage-outside) but it is not recommended.

<a name="usage-outside"></a>
##Usage outside the Asgard Framework
Here you will have to provide the dependencies yourself (see the next section):

	$entityFieldsSolver = new \Asgard\Entityform\EntityFieldsSolver;
	$request            = \Asgard\Http\Request::CreateFromGlobals();
	$entity             = new Article;
	$form = new \Asgard\Entityform\Entityform(
		$entity, #required
		[ #optional
			'action'  => 'form.php',
			'enctype' => 'multipart/form-data',
			'attrs'   => [
				'class' => 'formClass'
			]
		],
		$request, #optional, if not request is provided the form will automatically use \Asgard\Http\Request::createFromGlobals()
		$entityFieldsSolver #optional, Asgard can automatically retrieve an instance of EntityFieldsSolver
	);

<a name="relations"></a>
##Add entity relations

If the entity form was created from an entity having a "comments" relation, you embed it in the form with:

	$form->addRelation('comments');

This will add a field for selecting comments related to the entity. Works for all kinds of relations, "one" and "many".

<a name="save"></a>
##Save the entity

To save the entity, simple do:

	$form->save();

If there is a validation error, it will throw the exception \Asgard\Form\FormException. Refer to the [Form documentation](docs/form) to know how to handle exceptions and errors.

<a name="get"></a>
##Get the entity

	$form->getEntity();

<a name="solver"></a>
##EntityFieldSolver

In order to tell the entityform how to create fields from entity properties, you can use the Asgard\Entityform\EntityFieldsSolver class. By default it already handles Text, text, Double, Integer, Email, Boolean, Date, Datetime, File entity properties (all in Asgard\Entity\Property\\). If the EntityFieldsSolver does not know what type of field to create for a specific property, it will return a \Asgard\Form\Field\TextField field by default.

To extend the entityFieldsSolver add a callback which will return a form field object:

	$cb = function(\Asgard\Entity\Property $property) {
		if(get_class($property) == 'Asgard\Entity\Property\DateProperty')
			return new MyOwnDateField;
	};
	$fieldsSolver->add($cb);

For entity properties with multiple values, use:

	$cb = function(\Asgard\Entity\Property $property) {
		if(get_class($property) == 'Asgard\Entity\Property\DateProperty')
			return new \Asgard\Form\DynamicGroup;
	};
	$fieldsSolver->addMany($cb);

If the callback returns null it will be ignored.

You can also nest other solvers:

	$anotherFieldsSolver->addSolver($fieldsSolver);

If $anotherFieldsSolver cannot solve the field, it will ask to the nested solvers.

To solve a field from an entity property:

	$form->solve($Definition->getProperty('title'));

To get the EntityFieldSolver from a form:

	$fieldsSolver = $form->getEntityFieldsSolver();

To set the EntityFieldsSolver for a form:

	$form->addEntityFieldsSolver($cb);

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)