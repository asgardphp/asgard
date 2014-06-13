<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputArgument;

class GenerateTestsCommand extends \Asgard\Console\Command {
	protected $name = 'generate-tests';
	protected $description = 'Generate stub-tests for untested routes';

	protected function execute() {
		$asgard = $this->getAsgard();
		$dst = $this->input->getArgument('dst') ? $this->input->getArgument('dst'):$asgard['kernel']['root'].'/Tests/AutoTest.php';

		$tg = new \Asgard\Core\Generator\TestsGenerator($asgard);
		$count = $tg->generateTests($dst);
		if($count === false) {
			$this->output->writeln('<error>Tests generation failed.</error>');
			$this->output->writeln('Tests generation failed. Tests should first pass. Check with: ');
			$this->output->writeln('phpunit');
		}
		else
			$this->output->writeln('<info>'.$count.' tests have been generated in: '.realpath($dst).'</info>');
	}

	protected function getArguments() {
		return [
			['dst', InputArgument::OPTIONAL, 'Destination file. Defaults to: Tests/AutoTest.php'],
		];
	}
}