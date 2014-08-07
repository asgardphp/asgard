<?php
namespace Asgard\Http\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class GenerateTestsCommand extends \Asgard\Console\Command {
	protected $name = 'generate-tests';
	protected $description = 'Generate stub-tests for untested routes';
	protected $dir;

	public function __construct($dir) {
		$this->dir = $dir;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$asgard = $this->getContainer();
		$dst = $this->input->getArgument('dst') ? $this->dir.'/'.$this->input->getArgument('dst'):$this->dir.'/AutoTest.php';

		$tg = new \Asgard\Http\Generator\TestsGenerator($asgard);
		$count = $tg->generateTests($dst);
		if($count === false)
			$this->error('Tests generation failed. Tests should pass first. Check with the command: phpunit');
		else
			$this->info($count.' tests have been generated in: '.realpath($dst).'.');
	}

	protected function getArguments() {
		return [
			['dst', InputArgument::OPTIONAL, 'Destination file. Defaults to: '.$this->dir.'/AutoTest.php'],
		];
	}
}