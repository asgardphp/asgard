<?php
namespace Asgard\Translation\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Translation yaml export command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ExportYamlCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'translation:export-yaml';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Export new translations to a YAML file.';
	/**
	 * Translation dependency.
	 * @var \Asgard\Translation\Translation
	 */
	protected $translation;
	/**
	 * Directories to fetch translations from.
	 * @var array
	 */
	protected $directories;

	public function __construct(\Asgard\Translation\Translation $translation, $directories=null) {
		$this->translation = $translation;
		if(!is_array($directories))
			$directories = [$directories];
		$this->directories = $directories;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$srcLocale = $this->input->getArgument('srcLocale');
		$dstLocale = $this->input->getArgument('dstLocale');
		$file = $this->input->getArgument('file');

		$this->translation->load($srcLocale);
		$this->translation->load($dstLocale);

		$translations = $this->translation->getTranslator()->getCatalogue($srcLocale)->all('messages');

		$e = new \Asgard\Translation\Extractor;
		$e->addStrings(array_keys($translations));

		foreach($this->directories as $dir)
			$e->parseDirectory($dir);

		$res = $e->getList($this->translation->getTranslator(), $dstLocale);

		if(!$res)
			$this->comment('No translations to export.');
		else {
			$dumper = new \Symfony\Component\Yaml\Dumper;
			$yaml = $dumper->dump($res, 1);
			file_put_contents($file, $yaml);

			$this->info('Translations exported with success.');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['srcLocale', InputArgument::REQUIRED, 'Source locale.'],
			['dstLocale', InputArgument::REQUIRED, 'Destination locale.'],
			['file', InputArgument::REQUIRED, 'Destination file.'],
		];
	}
}