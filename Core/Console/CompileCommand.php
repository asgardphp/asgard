<?php
namespace Asgard\Core\Console;

class CompileCommand extends \Asgard\Console\Command {
	protected $name = 'compile';
	protected $description = 'Compile classes into one file for better performance';

	protected function execute() {
		$this->getApplication()->add(new \ClassPreloader\Command\PreCompileCommand);

		$app = $this->getAsgard();
		$this->outputPath = $app['kernel']['root'].'/storage/compiled.php';

		$classes = require __DIR__.'/compile/classes.php';

		$this->callSilent('compile', [
			'--config' => implode(',', $classes),
			'--output' => $this->outputPath,
			'--strip_comments' => 1,
		]);
	}
}