<?php
namespace Asgard\Core\Command;

use ClassPreloader\Factory;
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
		$container = $this->getContainer();

		if(!$this->compile) {
			$this->comment('Do no compile classes because of configuration (compile).');
			return;
		}

		$preloader = (new Factory)->create();

		$handle = $preloader->prepareOutput($this->compiledClassesFile);

		$files = require __DIR__.'/compile/classes.php';

		foreach ($classes as $file) {
			fwrite($handle, $preloader->getCode($file, true)."\n");
		}

		fclose($handle);

		$this->info('Classes have been compiled into: '.$this->compiledClassesFile.'.');
	}
}
