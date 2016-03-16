<?php
namespace Asgard\Translation\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Translation import command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ImportCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'translation:import';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Import a translations CSV file and export to a YAML file.';

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$src = $this->input->getArgument('src');
		$dst = $this->input->getArgument('dst');

		$sep = ';';

		$res = [];
		$f = fopen($src, "r");
		fgetcsv($f, 1000, $sep);
		while(($data = fgetcsv($f, 1000, $sep)) !== FALSE)
			$res[$data[0]] = $data[2];
		fclose($f);

		if(!$res)
			$this->comment('No translations to export.');
		else {
			$dumper = new \Symfony\Component\Yaml\Dumper();
			$yaml = $dumper->dump($res, 1);
			file_put_contents($dst, $yaml);

			$this->info('Translations imported with success.');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['src', InputArgument::REQUIRED, 'Source file.'],
			['dst', InputArgument::REQUIRED, 'Destination file.'],
		];
	}
}