#Config

[![Build Status](https://travis-ci.org/asgardphp/config.svg?branch=master)](https://travis-ci.org/asgardphp/config)

The Config package helps you manage the configuration of your application.

- [Installation](#installation)
- [Usage in the Asgard Framework](#usage-asgard)
- [Usage outside the Asgard Framework](#usage-outside)
- [methods](#methods)
- [structure](#structure)
- [Commands](#commands)

<a name="installation"></a>
##Installation
**If you are working on an Asgard project you don't need to install this library as it is already part of the standard libraries.**

	composer require asgard/config 0.*

<a name="usage-asgard"></a>
##Usage in the Asgard Framework

	$config = $container['config'];

The [container](docs/container) is often accessible as a method parameter or through a [ContainerAware](docs/container#containeraware) object. You can also use the [singleton](docs/container#usage-outside) but it is not recommended.

<a name="usage-outside"></a>
##Usage outside the Asgard Framework

	$config = new \Asgard\Config\Config;

<a name="methods"></a>
##Methods

Config inherits [\Asgard\Common\Bag](docs/bag) to access its data.

Besides, you can load a configuration with:

	$config->loadFile('file.yml');

Or a whole directory:

	$config->loadDir('config/');

This will load files that in the directory.

**Local files**

If you want to add configuration specific to your local setup, name the file as such:

	config.local.yml

The file will be loaded after others, and is ignored by default by the Asgard application .gitignore file.

**Environment files**

If you want to make configuration files specific to environments, name them as such:

	config_[env].yml

With [env] being the name of the environment.

Calling:

	$config->loadDir('config/', 'prod');

Will load default configuration files, plus all *_prod.yml files, while ignoring files like *_dev.yml

<a name="structure"></a>
##Configuration file structure

A configuration file, like config.yml contains an array of parameters in YAML format:

	database:
		host: localhost
		user: root
		password:
		database: asgard

Each key can be acessed like:

	$config->get('database.user');
	#or
	$config['database.user'];
	#or
	$config['database']['user'];

<a name="commands"></a>
##Commands

###Init

Initialize the configuration files.

Usage:

	php console config:init

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)