<?php
namespace Asgard\Console;

use Symfony\Component\Console\Input\InputOption;

/**
 * The Asgard Console Application
 * @author Michel Hognerud <michel@hognerud.net>
 * @api
*/
class Application extends \Symfony\Component\Console\Application {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Constructor.
	 * @param string                      $name
	 * @param string                      $version
	 * @param \Asgard\Container\ContainerInterface $container The application container
	 * @api
	*/
	public function __construct($name, $version=null, $container=null) {
		$this->container = $container;
		parent::__construct($name, $version);
		$this->setCatchExceptions(false);

	}

	/**
	 * Return the default input definition.
	 * @return \Symfony\Component\Console\Input\InputDefinition
	*/
	protected function getDefaultInputDefinition() {
		$definition = parent::getDefaultInputDefinition();
		$definition->addOption(new InputOption('--env', null, InputOption::VALUE_OPTIONAL, 'The environment the console should run under.'));

		return $definition;
	}
}