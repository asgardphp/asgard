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
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->getApplication()->add(new \ClassPreloader\Command\PreCompileCommand);

		$container = $this->getContainer();
		$outputPath = $container['kernel']['root'].'/storage/compiled.php';

		$classes = require __DIR__.'/compile/classes.php';

		$this->callSilent('compile', [
			'--config' => implode(',', $classes),
			'--output' => $outputPath,
			'--strip_comments' => 1,
		]);
	}
}