#HTTP

[![Build Status](https://travis-ci.org/asgardphp/http.svg?branch=master)](https://travis-ci.org/asgardphp/http)

Library to handle HTTP requests, routing, controllers and responses.

##Installation
**If you are using the Asgard Framework you don't need to install this library as it part of the default libraries that Asgard uses.**

In your composer file:

    "require": {
        "asgard/http": "0.*"
	}

##Request & Response

Handling HTTP requests and building responses. [See the documentation.](http://asgardphp.com/docs/http-requestresponse)

	$request = \Asgard\Http\Request::createFromGlobals();
	//...
	return (new \Asgard\Http\Response)->setCode(404)->setContent('not found :(');

##Controllers

Structure your code around controllers. [See the documentation.](http://asgardphp.com/docs/http-controllers)

	/**
	 * @Prefix("products")
	 */
	class ProductController extends \Asgard\Http\Controller {
		/**
		 * @Prefix("index")
		 */
		public function indexAction(\Asgard\Http\Request $request) {
			//...
		}

		/**
		 * @Prefix("search")
		 */
		public function searchAction(\Asgard\Http\Request $request) {
			//...
		}
	}

##Utils

The package comes with tools. [See the documentation.](http://asgardphp.com/docs/http-utils)

###HTML

	#in the controller or the page view
	$html->includeJS('query.js');
	$html->includeCSS('style.css');

	#in the layout view
	$html->printAll();

###Flash

	#in the controller
	$flash->addSuccess('Hurray!);

	#in the view
	$flash->showAll();

###Browser

	$container = \Asgard\Container\Container::singleton();
	$browser = new \Asgard\Http\Browser\Browser($container);
	$browser->getSession()->set('admin_id', 123);
	
	$response = $browser->post('admin/news/new');

	if($response->getCode() != 200)
		echo 'error..';
	else
		echo $response->getContent();

##Commands

[List of commands that come with the HTTP package.](http://asgardphp.com/docs/http-commands)

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)