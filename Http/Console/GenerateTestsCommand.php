<?php
namespace Asgard\Http\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GenerateTestsCommand extends \Asgard\Console\Command {
	protected $name = 'generate-tests';
	protected $description = 'Generate stub-tests for untested routes';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$asgard = $this->getAsgard();
		$dst = $input->getArgument('dst') ? $input->getArgument('dst'):$asgard['kernel']['root'].'/Tests/AutoTest.php';

		$tg = new \Asgard\Http\Generator\TestsGenerator($asgard);
		$count = $tg->generateTests($dst);
		if($count === false) {
			$output->writeln('<error>Tests generation failed.</error>');
			$output->writeln('Tests generation failed. Check with: ');
			$output->writeln('phpunit --bootstrap '.$asgard['kernel']['root'].'/Tests/bootstrap.php '.$asgard['kernel']['root'].'/Tests');
		}
		else
			$output->writeln('<info>'.$count.' tests have been generated in: '.realpath($dst).'</info>');
	}

	protected function getArguments() {
		return array(
			array('dst', InputArgument::OPTIONAL, 'Destination file. Defaults to: Tests/AutoTest.php'),
		);
	}
}