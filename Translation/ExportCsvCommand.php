<?php
namespace Asgard\Translation;

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
	 * @var \Symfony\Component\Translation\TranslatorInterface
	 */
	protected $translator;
	/**
	 * Directories to fetch translations from.
	 * @var array
	 */
	protected $directories;
	/**
	 * Translation resources.
	 * @var \Asgard\Core\translationResources
	 */
	protected $translationResources;

	public function __construct(\Asgard\Core\translationResources $translationResources, \Symfony\Component\Translation\TranslatorInterface $translator, $directories=null) {
		$this->translationResources = $translationResources;
		$this->translator = $translator;
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

		$translations = [];
		$container = $this->getContainer();
		$yaml = new \Symfony\Component\Yaml\Parser;

		$translator = $this->translator;
		$translator->addLoader('array', new \Symfony\Component\Translation\Loader\ArrayLoader);

		$translationResources = $this->translationResources;

		$srcFiles = $translationResources->getFiles($srcLocale);
		$dstFiles = $translationResources->getFiles($dstLocale);

		foreach($srcFiles as $file) {
			$_translations = $yaml->parse(file_get_contents($file));
			$translations = array_merge($translations, $_translations);
			$translator->addResource('array', $_translations, $srcLocale);
		}
		foreach($dstFiles as $file)
			$translator->addResource('yaml', $file, $dstLocale);

		$e = new Extractor;
		$e->addStrings(array_keys($translations));

		foreach($this->directories as $dir)
			$e->parseDirectory($dir);

		$res = $e->getListWithTranslation($this->translator, $srcLocale, $dstLocale);

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