<?php
namespace Asgard\Translation;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Tester curl command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CurlCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'tester:curl';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Convert a curl query to an Asgard request.';

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$curl = $this->input->getArgument('curl');

		$conv = new \Tester\CurlConverter;

		$output->writeln($conv->convert($curl));
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['curl', InputArgument::REQUIRED, 'Curl query.'],
		];
	}
}