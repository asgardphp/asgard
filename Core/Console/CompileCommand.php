<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CompileCommand extends \Asgard\Console\Command {
	protected $name = 'compile';
	protected $description = 'Compile classes into one file for better performance';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->getApplication()->add(new \ClassPreloader\Command\PreCompileCommand);

		$app = $this->getAsgard();
		$outputPath = $app['kernel']['root'].'/storage/compiled.php';

		$classes = require __DIR__.'/compile/classes.php';

		$this->callSilent('compile', [
			'--config' => implode(',', $classes),
			'--output' => $outputPath,
			'--strip_comments' => 1,
		]);
	}
}