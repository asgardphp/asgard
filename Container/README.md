#Container

[![Build Status](https://travis-ci.org/asgardphp/container.svg?branch=master)](https://travis-ci.org/asgardphp/container)

The container provides services to the application. In the Asgard framework, the container is often stored in the container variable.

- [Installation](#installation)
- [Usage in the Asgard Framework](#usage-asgard)
- [Usage outside the Asgard Framework](#usage-outside)
- [Registering a service](#registering)
- [Accessing a service](#accessing)
- [Creating a new service instance](#creating)
- [Checking if a service exists](#checking)
- [Removing a service](#removing)
- [ContainerAware Trait](#containeraware)
- [Commands](#commands)

<a name="installation"></a>
##Installation
**If you are using the Asgard Framework you don't need to install this library as it part of the default libraries that Asgard uses.**

In your composer file:

    "require": {
        "asgard/container": "dev-master"
	}

<a name="usage-asgard"></a>
##Usage in the Asgard Framework

Inside the framework, the container is often accessible as a parameter or through a [ContainerAware](#containeraware) object. You can also use the singleton (see below) but it is not recommended.

<a name="usage-outside"></a>
##Usage outside the Asgard Framework

	$container = new \Asgard\Container\Container;
	#or
	$container = new \Asgard\Container\Container::singleton();

<a name="registering"></a>
##Registering a service

	$container->register('cache', function($container, $param) {
		return new \Cache($param);
	});
	#or
	$container['cache'] = new \Cache($param);
	#or
	$container->set('cache', new \Cache($param));

<a name="accessing"></a>
##Accessing a service

	$cache = $container->get('cache', [$param]);
	#or
	$cache = $container['cache'];

If you call it multiple times, the container will make sure the same instance is returned every time.

<a name="creating"></a>
##Creating a new service instance

	$cache = $container->make('cache', [$param]);

<a name="checking"></a>
##Checking if a service exists

	$container->has('cache');
	#or
	isset($container['cache']);

<a removing="usage"></a>
##Removing a service

	$container->remove('cache');
	#or
	unset($container['cache']);

<a name="containeraware"></a>
##ContainerAware Trait

This trait provides two methods:

- setContainer($container)
- getContainer()

and a protected member variable "$container".

To use it, add the following line in a class just after the opening bracket

	use \Asgard\Container\ContainerAware;

<a name="commands"></a>
##Commands

###ListCommand

Show all the services loaded in the application.

Usage:

	php console services [--defined] [--registered]

--defined: to show where a service was defined

--registered: to shown where a service was registered

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)