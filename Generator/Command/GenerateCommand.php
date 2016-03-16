<?php
namespace Asgard\Generator\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generate command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class GenerateCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'generate';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Generate bundles from a single YAML file';
	/**
	 * Generator engine.
	 * @var \Asgard\Generator\GeneratorEngineInterface
	 */
	protected $generatorEngine;

	public function __construct(\Asgard\Generator\GeneratorEngineInterface $generatorEngine) {
		$this->generatorEngine = $generatorEngine;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getContainer();
		$path = $this->input->getArgument('path');
		$root = $container['kernel']['root'].'/';

		$yaml = new \Symfony\Component\Yaml\Parser();
		$bundles = $yaml->parse(file_get_contents($path));
		if(!is_array($bundles))
			throw new \Exception($path.' is invalid.');

		$overrideFiles = $this->input->getOption('override-files');
		$this->generatorEngine->setOverrideFiles($overrideFiles);

		$this->generatorEngine->generate($bundles, $root);

		$this->info('Bundles created: '.implode(', ', array_keys($bundles)));
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['path', InputArgument::REQUIRED, 'Path to the YAML file']
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getOptions() {
		return [
			['override-files', null, InputOption::VALUE_NONE, 'Override existing files', null],
			['skip', null, InputOption::VALUE_NONE, 'Skip existing bundles', null],
		];
	}
}