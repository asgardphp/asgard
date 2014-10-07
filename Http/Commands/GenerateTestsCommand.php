<?php
namespace Asgard\Http\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generate tests command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class GenerateTestsCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'generate-tests';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Generate stub-tests for untested routes';
	/**
	 * Tests directory.
	 * @var string
	 */
	protected $dir;

	/**
	 * Constructor.
	 * @param string $dir
	 */
	public function __construct($dir) {
		$this->dir = $dir;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
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

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['dst', InputArgument::OPTIONAL, 'Destination file. Defaults to: '.$this->dir.'/AutoTest.php'],
		];
	}
}