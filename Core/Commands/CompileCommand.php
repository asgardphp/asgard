<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Compile command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CompileCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'compile';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Compile classes into one file for better performance';
	/**
	 * Flag to compile classes.
	 * @var boolean
	 */
	protected $compile;
	/**
	 * Compiled classes file path.
	 * @var string
	 */
	protected $compiledClassesFile;

	/**
	 * Constructor.
	 * @param boolean $compile
	 * @param string  $compiledClassesFile
	 */
	public function __construct($compile, $compiledClassesFile) {
		$this->compile = $compile;
		$this->compiledClassesFile = $compiledClassesFile;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->getApplication()->add(new \ClassPreloader\Command\PreCompileCommand);

		$container = $this->getContainer();
		if(!$this->compile) {
			$this->comment('Do no compile classes because of configuration (compile).');
			return;
		}

		$outputPath = $this->compiledClassesFile;

		$classes = require __DIR__.'/compile/classes.php';

		$this->callSilent('compile', [
			'--config' => implode(',', $classes),
			'--output' => $outputPath,
			'--strip_comments' => 1,
		]);
		$this->info('Classes have been compiled into: '.$outputPath.'.');
	}
}