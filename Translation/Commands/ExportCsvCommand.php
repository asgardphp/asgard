<?php
namespace Asgard\Translation\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Transltion csv export command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ExportCsvCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'translation:export-csv';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Export new translations to a CSV file.';
	/**
	 * Translator dependency.
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

		$container = $this->getContainer();

		$this->translation->load($srcLocale);
		$this->translation->load($dstLocale);

		$translations = $this->translation->getTranslator()->getCatalogue($srcLocale)->all('messages');

		$e = new \Asgard\Translation\Extractor;
		$e->addStrings(array_keys($translations));

		foreach($this->directories as $dir)
			$e->parseDirectory($dir);

		$res = $e->getListWithTranslation($this->translation->getTranslator(), $srcLocale, $dstLocale);

		if(!$res)
			$this->comment('No translations to export.');
		else {
			$csv = new \H0gar\Csv\Csv([
				'Key',
				'Source',
				'Translation'
			]);
			foreach($res as $r)
				$csv->add($r);
			file_put_contents($file, $csv->render());

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