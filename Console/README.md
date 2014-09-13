#Console

The Asgard Console package is an simple extension to the [Symfony Console component](http://symfony.com/fr/doc/current/components/console/introduction.html).

- [Installation](#installation)
- [Commands](#usage)
- [Command](#command)
- [Application](#application)

<a name="installation"></a>
##Installation
**If you are working on an Asgard project you don't need to install this library as it is already part of the standard libraries.**

	composer require asgard/console 0.*

<a name="usage"></a>
##Usage

MyCommand class

	class MyCommand extends Asgard\Console\Command {
		protected $name = 'mycommand';
		protected $description = 'This is my command';

		protected function getOptions() {
			return [
				['verbose', null, InputOption::VALUE_NONE, 'Verbose output.', null]
			];
		}

		protected function getArguments() {
			return [
				['argument', InputArgument::REQUIRED, 'An argument.'],
			];
		}
	}

Console script:

	$command = new MyCommand; #extends Asgard\Console\Command
	containerlication = new MyApplication('MyApp', 5.6, new \Asgard\Container\Container); #extends Asgard\Console\Application
	#application constructor parameters are optional
	containerlication->add($command);

Command:

	php console mycommand theargument --verbose

<a name="command"></a>
##Command methods

Get the services container

	$this->getContainer();

Call another command

	$this->call('another-command', $arguments=[]);

Call another command silently (no output)

	$this->callSilent('another-command', $arguments=[]);

Ask for confirmation

	$this->confirm('Are you sure?');

Display an information

	$this->info('A message');

Display an error

	$this->error('A message');

Display a comment

	$this->comment('A message');

Display a question

	$this->question('A message');

<a name="application"></a>
##Application methods

	$container = $this->getContainer();

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)