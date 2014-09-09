#Form

[![Build Status](https://travis-ci.org/asgardphp/form.svg?branch=master)](https://travis-ci.org/asgardphp/form)

The Form library makes it very easy to build forms, render them, remember inputs after submission and automatically process it, validate and protected from CSRF attacks.

- [Installation](#installation)
- [Usage in the Asgard Framework](#usage-asgard)
- [Usage outside the Asgard Framework](#usage-outside)
- [Form dependencies](#dependencies)
- [Form options](#options)
- [HTTP Methods](#http-methods)
- [Fields](#fields)
- [Groups and Forms](#groups)
- [Dynamic Groups](#dynamic)
- [Save and Validation](#validation)
- [CSRF Protection](#csrf)
- [Form data](#data)
- [Rendering](#rendering)
- [Managing widgets](#managing-widgets)
- [Form dependencies](#dependencies)
- [Examples](#examples)

<a name="installation"></a>
##Installation
**If you are using the Asgard Framework you don't need to install this library as it part of the default libraries that Asgard uses.**

In your composer file:

    "require": {
        "asgard/form": "0.*"
	}

<a name="usage-asgard"></a>
##Usage in the Asgard Framework

The advantage of using the form service is that it will provides the Form with all the necessary dependencies:

	$container->make('form', [
		'name', #name (optional)
		[ #params (optional)
			'action'  => 'form.php',
			'enctype' => 'multipart/form-data',
			'attrs'   => [
				'class' => 'formClass'
			]
		],
		\Asgard\Http\Request::CreateFromGlobals(), #request (optional, Asgard will feed the form with the current request)
		[ #fields (optional)
			'name'    => new TextField(),
			'content' => new TextField(),
		]
	]);

In a controller (or any class using the view ContainerAware), $container is available through $this->getContainer(). You can also access it by \Asgard\Container\Container::singleton().

<a name="usage-outside"></a>
##Usage outside the Asgard Framework

Here you will have to provide the dependencies yourself (see the next section):

	$form = new \Asgard\Form\Form(
		'name', #name (optional)
		[ #params (optional)
			'action'  => 'form.php',
			'enctype' => 'multipart/form-data',
			'attrs'   => [
				'class' => 'formClass'
			]
		],
		\Asgard\Http\Request::CreateFromGlobals(), #request (optional)
		[ #fields (optional)
			'name'    => new TextField(),
			'content' => new TextField(),
		]
	);

*If no enctype is provided, the form will automatically use "multipart/form-data" if it contains files*

<a name="dependencies"></a>
##Form dependencies

###Request

	$form->setRequest(\Asgard\Http\Request::createFromGlobals());

###Translator

	$form->setTranslator(new \Symfony\Component\Translation\Translator('en'));

###Services container

	$form->setContainer(new \Asgard\Container\Container);

The services container might be necessary for some fields or widgets.

<a name="options"></a>
##Form options

To access or modify a form parameters:

	$param = $form->getOption('action');
	$form->setOption('action', $action);

Some common parameters are:

- action (e.g. /url/to/page)
- enctype (e.g. multipart/form-data)

<a name="http-method"></a>
##Form HTTP Method

By default the form gets its inputs from POST requests, but if you want to make a form that works with GET inputs only:

	$form->setMethod('get');

<a name="fields"></a>
##Fields

A field is a single input of the form. All fields extend the \Asgard\Form\Field class. [Here is a list](#list-of-fields) of all the available fields at the moment.

###Validation

To add specific validation rules to a field, use the 'validation' option:

	$form['field'] = new \Asgard\Form\Fields\TextField(['validation'=>[
		'minlength' => 5,
		'maxlength' => 5,
	]]);

The validation rules are detailed in the [validation documentation](http://asgardphp.com/docs/validation).

###Field default value

To set a default value for a field, use the 'default' option:

	$form['field'] = new \Asgard\Form\Fields\TextField(['default'=>'placeholder']);

###Render a field

Fields use widgets to be rendered. Each type of field uses a default widget but you can sometimes use a different one. See below in the list of fields for different ways to render a field.

To render a field with the default widget though, use:

	echo $form['field']->def();

###Widgets

Widgets are identified by a short name such as "text", "password", "select", etc. and are called when rendering a field like:

	echo $form['field']->text();

"text" being the name of the widget we want to use to render the field.

By default, the Form will look for the corresponding widget in \Asgard\Form\Widgets. [But it is possible to manage what widgets are available](#managing-widgets).

###Field label

To show the label of a field:

	echo $form['field']->label();

Or the whole label tag:

	echo $form['field']->labelTag();

<a name="list-of-fields"></a>
###List of Fields

**boolean**

	$form['field'] = new \Asgard\Form\Fields\BooleanField;

displays a checkbox.

**country**

	$form['field'] = new \Asgard\Form\Fields\BooleanField;

displays a select field with a list of all countries.

**csrf**

	$form['field'] = new \Asgard\Form\Fields\CSRFField;

adds a hidden field to the form to prevent CSRF attack.

**date**

	$form['field'] = new \Asgard\Form\Fields\DateField;

Render three select fields (day/month/year):

	$form['field']->def();
	#or
	$form['field']->date();

Render text field:

	$form['field']->text();

**datetime**

	$form['field'] = new \Asgard\Form\Fields\DatetimeField;

Render six select fields (second/minute/hour/day/month/year):

	$form['field']->def();
	#or
	$form['field']->datetime();

Render text field:

	$form['field']->text();

**time**

	$form['field'] = new \Asgard\Form\Fields\TimeField;

Render three select fields (second/minute/hour):

	$form['field']->def();
	#or
	$form['field']->time();

Render text field:

	$form['field']->text();

**day**

	$form['field'] = new \Asgard\Form\Fields\DayField;

adds a single select field with days from 1 to 31.

**file**

	$form['field'] = new \Asgard\Form\Fields\FileField;

adds a file field.

**hidden**

	$form['field'] = new \Asgard\Form\Fields\HiddenField;

adds an hidden field to the form.

**month**

	$form['field'] = new \Asgard\Form\Fields\MonthField;

adds a single select field with months from 1 to 12.

**multipleselect**

	$form['field'] = new \Asgard\Form\Fields\SelectField(['choices'=>['bob', 'joe', 'david']);

Render multiple select:

	$form['field']->def();
	#or
	$form['field']->multipleselect();

Render checkboxes:

	$form['field']->checkboxes();
	#or
	foreach($form['field']->getCheckboxes() as $checkbox)
		echo $checkbox->label().': '.$checkbox;

**select**

	$form['field'] = new \Asgard\Form\Fields\SelectField(['choices'=>['bob', 'joe', 'david']);

adds a single select field.

**text**

	$form['field'] = new \Asgard\Form\Fields\TextField;

**year**

	$form['field'] = new \Asgard\Form\Fields\YearField;

adds a single select field with the last 50 years.

<a name="groups"></a>
##Groups and Forms
A Group is a group of fields. For example you may have a group for the shipping address fields and another group with the billing address fields. You may also have groups containing other groups. A form itself is a group which contains groups and fields.

To know how many fields or sub-groups a Group or a Form has:

	$count = $group->size();

Forms and groups are built like arrays. To add a field:

	$form['title'] = new \Asgard\Form\Fields\TextField;

To get its value after the form was submitted:

	$value = $form['title']->value();

You can add a whole group to a form:

	$form['address'] = [
		'number' => new Fields\TextField,
		'street' => new Fields\TextField,
		'city' => new Fields\TextField,
		'zipcode' => new Fields\TextField
	];

In the form, the array will be converted to a \Asgard\Form\Group object. You can access a group's fields like a form:

	$value = $form['address']['street']->value();

As a form is also a Group, you can also embed forms:

	$personForm = new \Asgard\Form\Form;
	$personForm['firstname'] = new Fields\TextField;
	$personForm['lastname'] = new Fields\TextField;

	$form['person'] = $personForm;

And access fields like:

	$form['person']['firstname']->value();

You can as well loop through a group or a form fields:

	foreach($group as $field) {
		// do something with the field
	}

<a name="dynamic"></a>
##Dynamic Groups

###Usage
Dynamic groups are very useful when you have an indefinite number of fields in a group. For example, a form in which the user could add as many names as he wants, with one name per field.

To add a dynamic group, use:

	$callback = function($data) {
		return new \Asgard\Http\Fields\TextField;
	};
	$form['names'] = \Asgard\Form\DynamicGroup($callback);

This will create a TextField automatically for each input in the group "names". If the user sends 5 names, the form will adapt itself and create 5 TextField to contain the 5 names.

###Prefill
You can even prefill a dynamic group:

	$form['names'][] =  new \Asgard\Http\Fields\TextField(['default'=>'name1']);
	$form['names'][] =  new \Asgard\Http\Fields\TextField(['default'=>'name2']);

###Rendering

	echo $form->open();
	foreach($form['names'] as $field)
		echo $field->def();
	echo $form->submit();
	echo $form->close();

You can set a lambda function to render a field of the dynamic group:

	$form['names']->setDefaultRender(function($field) {
		return $field->label().': '.$field->def();
	});

You would then have the following code in the view:

	echo $form->open();
	foreach($form['names'] as $field)
		echo $form['names']->def($field);
	echo $form->submit();
	echo $form->close();

###Using jQuery to handle multiple fields on the front-end

To let the user add fields by himself, use the following snippet:

	<script>
	function add() {
		var newfield = $('<?php echo $form['names']->renderTemplate("'+$('.name').length+'") ?>');
		$('#slides').append(newfield);
	}
	</script>

	echo $form->open();
	echo '<div id="names">';
	foreach($form['names'] as $field)
		echo '<div>'.$field->label().': '.$field-def(['attrs'=>['class'=>'name']]).'</div>';
	echo '</div>';

	<input type="button" name="add" value="Add a name" onclick="add()">
	echo $form->submit();
	echo $form->close();

The method renderTemplate generates a javascript template for create new HTML fields when the user clicks on the "add" button.

<a name="validation"></a>
##Save and Validation

To check that a form was sent:

	$form->sent(); #returns true|false

To check that the inputs are valid:

	$form->isValid(); #returns true|false

To get the errors:

	$errors = $form->errors();

To get a field or a group errors:

	$errors = $form['title']->errors();

To get only general errors or errors that do not belong to a specific field:

	$errors = $form->getGeneralErrors();

To save a form:

	$form->save();

In case of an error, an exception will be raised:

	try {
		$form->save();
	} catch(\Asgard\Form\FormException $e) {
		//...
	}

If all is valid, it will execute the form's doSave() method, which by default does nothing. It will also try to save each nested-form.

However you can create your own classes that extend the Form class and override the "doSave" method or use a callback:

	$form->setPreSaveCallback(function($form) {
		// do something before validation
	});
	$form->setSaveCallback(function($form) {
		// do something to save the form
	});
	$form->save()

<a name="csrf"></a>
##CSRF Protection

To enable CSRF protection for a form:

	$form->csrf();

To disable it:

	$form->csrf(false);

If the form will be invalid if sent without the CSRF token. The error will be available through $form->getGeneralErrors() or $form['_csrf_token']->error().

<a name="data"></a>
##Form Data

Get data from a form after it has been sent:

	$data = $form->data();

Reset data:

	$form->reset();

Set data manually:

	$form->setData(['title' => 'abc']); 

<a name="rendering"></a>
##Rendering a form

	echo $form->open($params=[]); #prints the opening tag. Params will override the parameters passed to the form instancefd

	//render fields e.g. $form['title']->def();

	echo form->submit('Send'); #prints a simple submit button with text "Send"
	echo $form->close(); #prints the closing tag and an hidden input for csrf if enabled

<a name="managing-widgets"></a>
##Managing widgets

###Instance

In the Asgard framework, you can use the service:

	$wm = $container['widgetsManager'];

Otherwise, you can also access a form widgetsManager through:

	$wm = $form->getWidgetsManager();

###Register a widget

	$wm->setWidget('text', 'MyClasses\Widgets\TextWidget');

###Register a namespace

	$wm->addNamespace('MyClasses\Widgets');

When using an unknown widget, the widgets manager will try to look for the widget in all registered namespaces. If the class MyClasses\Widgets\TextWidget exists, it will be used to render the field.

###Example in Asgard

	$container['widgetsManager']->setWidget('text', 'MyClasses\Widgets\TextWidget');

	$form = $container->make('form');
	$form['title'] = new \Asgard\Fields\TextField;
	echo $form['title']->text();

###Example in standalone form

	$form = new \Asgard\Form\Form;
	$form->getWidgetsManager()->setWidget('text', 'MyClasses\Widgets\TextWidget');
	$form['title'] = new \Asgard\Fields\TextField;
	echo $form['title']->text();

<a name="examples"></a>
##Examples

###User registration

Building the form:

	$form = $container->make('form', ['user']);
	#or
	$form = new \Asgard\Form\Form('user');

	$form['username'] = new \Asgard\Form\Fields\TextField(['validation'=>'required']);
	$form['password'] = new \Asgard\Form\Fields\TextField(['validation'=>'required', 'widget' => 'password']);

	if($form->sent()) {
		if(!($errors = $form->errors())) {
			$username = $form['username']->value();
			$password = $form['password']->value();
			// save in database..
			echo 'Good :)';
		}
		else {
			echo 'Bad :(';
			foreach($e->errors() as $error)
				echo "\n".$error;
		}
	}

Showing the form:

	echo $form->open();
	echo $form['username']->def();
	echo $form['password']->password();
	echo $form->close();


###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)