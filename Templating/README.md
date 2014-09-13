#Templating

[![Build Status](https://travis-ci.org/asgardphp/templating.svg?branch=master)](https://travis-ci.org/asgardphp/templating)

Templating is a simple package which provides interfaces to build your own templating system. It also provides a PHP templating system and the Viewable class.

- [Installation](#installation)
- [Interfaces](#interfaces)
- [PHP Template](#php)
- [Viewable](#viewable)

<a name="installation"></a>
##Installation
**If you are working on an Asgard project you don't need to install this library as it is already part of the standard libraries.**

	composer require asgard/templating 0.*

<a name="interfaces"></a>
##Interfaces

###TemplateEngineInterface

	public function templateExists($template); #check that a template exists
	public function getTemplateFile($template); #return the file corresponding to the template name
	public function createTemplate(); #return a new instance of the template class (implementing TemplateInterface)

###TemplateInterface

	public function getEngine();
	public function setEngine(TemplateEngineInterface $engine);
	public function setTemplate($template);
	public function getTemplate();
	public function setParams(array $params=[]);
	public function getParams();
	public function render($template=null, array $params=[]);
	public static function renderFile($file, array $params=[]);

<a name="php"></a>
##PHP Template

The PHPTemplate class implements the TemplateInterface.

Create a new template:

	$template = new Asgard\Templating\PHPTemplate('template.php', ['title'=>'Hello!']);

Set a template

	$template->setTemplate('template2.php');

Get the template:

	$template->getTemplate(); #template2.php

Set parameters:

	$template->setParams(['title'=>'Hello!']);

Get parameters:

	$template->getParams(); #['title'=>'Hello!']

Render the template:

	$template->render();

Render a specific template with parameters:

	$template->render('template.php', ['title'=>'Hello!']);

Statically render a template:

	Asgard\Templating\PHPTemplate::renderFile('template.php', ['title'=>'Hello!']);

<a name="viewable"></a>
##Viewable

The Viewable trait provides the methods so that a class can easily be rendered with templates.

###Usage

	class Abc {
		use \Asgard\Templating\Viewable;

		public function test($param1, $param2) {
			return 'test';
		}
	}

	$abc = new Abc;

	$abc->setTemplateEngine($engine);
	#the engine will be passed to any template used by the class
	#$abc->getTemplateEngine(); to get the engine

	$abc->run('test', ['param1', 'param2']);

###Rendering
There are many ways a method can render the result.

	public function test() {
		return 'test';
	}
	#run('test') returns 'test'

	public function test() {
		echo 'test';
	}
	#run('test') returns 'test'

	public function test() {
		return new MyTemplate('template.php', [/*..*/]);
	}
	#run('test') will call ->render() on the template and return the result

	public function test() {
		$this->view = new MyTemplate('template.php', [/*..*/]);
	}
	#run('test') will call ->render() on $this->view and return the result

	public function test() {
		$this->view = 'template';
	}
	#if the object as a TemplateEngine, it will create a template instance and pass 'template.php' to it.
	#if not, Viewable will use its own default rendering technique.

###Default rendering

When a template name is passed to $this->view, and the object does not have its own TemplateEngine, Viewable will try to solve the template file corresponding to the template name, include it and pass its own variables.

For example:

	#Viewable class test method
	public function test() {
		$this->title = 'Hello!';
		$this->view = 'template'; #template matches /var/www/project/templates/template.php
	}

	#template.php
	echo '<h1>'.$title.'</h1>';

Will return:

	<h1>Hello!</h1>

You can help the viewable object solves the template file with:

	$abc->addTemplatePathSolver(function($obj, $template) {
		$file = '/var/www/project/templates/'.$template.'.php';
		if(file_exists($file))
			return $file;
	});

###Static rendering

	Abc::fragment('tes', [$param1, ...]);

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)