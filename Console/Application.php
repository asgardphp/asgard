<?php
namespace Asgard\Console;

use Symfony\Component\Console\Input\InputOption;

/**
 * The Asgard Console Application
 * 
 * @author Michel Hognerud <michel@hognerud.net>
*/
class Application extends \Symfony\Component\Console\Application {
	protected $asgard;
	
	/**
	 * Constructor.
	 * 
	 * @param \Asgard\Container\Container asgard The application container
	*/
	public function __construct($name, $version, $asgard) {
		$this->asgard = $asgard;
		parent::__construct($name, $version);
		$this->setCatchExceptions(false);

	}

	/**
	 * Returns the default input definition.
	 * 
	 * @return \Symfony\Component\Console\Input\InputDefinition
	*/
	protected function getDefaultInputDefinition() {
		$definition = parent::getDefaultInputDefinition();
		$definition->addOption(new InputOption('--env', null, InputOption::VALUE_OPTIONAL, 'The environment the console should run under.'));

		return $definition;
	}
	
	/**
	 * Returns the asgard application container.
	 * 
	 * @return \Asgard\Container\Container The application container.
	*/
	public function getContainer() {
		return $this->asgard;
	}
}