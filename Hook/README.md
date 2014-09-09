#Hook

[![Build Status](https://travis-ci.org/asgardphp/hook.svg?branch=master)](https://travis-ci.org/asgardphp/hook)

If you have ever used an event manager, you will find the Hooks component very similar. With the HooksManager you can create hooks, on which you can hook callbacks to be executed when the hooks are triggered.

- [Installation](#installation)
- [Usage in the Asgard Framework](#usage-asgard)
- [Usage outside the Asgard Framework](#usage-outside)
- [Create a hook](#create)
- [Trigger a hook](#trigger)
- [Executing callbacks before and after hooks](#executing)
- [Filters](#filters)
- [The HooksChain Object](#hookschain)
- [HooksContainer](#hookscontainer)

<a name="installation"></a>
##Installation
**If you are using the Asgard Framework you don't need to install this library as it part of the default libraries that Asgard uses.**

In your composer file:

    "require": {
        "asgard/hook": "0.*"
	}

<a name="usage-asgard"></a>
##Usage in the Asgard Framework

	$hm = $container['hooks'];
	
The [container](http://asgardphp.com/docs/container) is often accessible as a parameter or through a [ContainerAware](http://asgardphp.com/docs/container#containeraware) object. You can also use the [singleton](http://asgardphp.com/docs/container#usage-outside) but it is not recommended.

<a name="usage-outside"></a>
##Usage outside the Asgard Framework

	$hm = new \Asgard\Hook\HooksManager;

<a name="create"></a>
##Create a hook

	$hm->hook('name_of_hook', function($chain, $param1) {
		// ...
	});

The first parameter is always a \Asgard\Hook\HooksChain object. The next ones are passed when the hook is triggered.

<a name="trigger"></a>
##Trigger a hook

	$hm->trigger('name_of_hook', [$param]);

If you want to execute your own function when calling trigger, use the last argument:

	$hm->trigger('name_of_hook', [$param], function($chain, $param) {
		// ...
	});

<a name="executing"></a>
##Executing callbacks before and after hooks
To execute functions before a hook:

	$hm->hookBefore('name_of_hook', function($chain, $param) {
		// ...
	});

And after:

	$hm->hookAfter('name_of_hook', function($chain, $param) {
		// ...
	});

<a name="filters"></a>
##Filters
Hooks can be used as filters when parameters are passed by reference

	$hm->hook('name_of_hook', function($chain, &$param) {
		$param = 123;
	});
	$hm->trigger('name_of_hook', [&$param]);

<a name="hookschain"></a>
##The HooksChain object
The chain contains all the callbacks to be executed in a hook. If a function returns a value, the chain stops and the value is returned by the trigger method.

The chain call also be stopped by calling:

	$chain->stop();

The function calling the stop method will be the last one to be executed.

To know how many functions have been executed in a hook:

	$hm->trigger('name_of_hook', [$param], null, $chain);
	$count = $chain->executed;

Here we provide a reference to retrieve the chain object and its executed property.

<a name="hookscontainer"></a>
##HooksContainer
A HooksContainer is a class containing hooks. It extends Asgard\Hook\HooksContainer and contains methods that are matched to hooks with annotations.

Example:

	<?php
	namespace Bundle\Hooks;

	class SomeHooks extends \Asgard\Hook\HooksContainer {
		/**
		 * @Hook("Asgard.Http.Start")
		 */
		public static function start($chain, $request) {
			//do something when HTTP starts processing a request
		}
	}

Here the method start is executed when the hook "Asgard.Http.Start" is triggered.

If you are working with a Asgard project, all HooksContainer in the Hooks/ folder of a bundle are automatically loaded.

If not, you can register the hooks with:

	$hooks = \Bundle\Hooks\SomeHooks::fetchHooks();
	$hooksManager->hooks($hooks);

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)