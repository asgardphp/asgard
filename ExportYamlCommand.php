<?php
namespace Asgard\Translation;

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
	 * Translator dependency.
	 * @var \Symfony\Component\Translation\TranslatorInterface
	 */
	protected $translator;
	/**
	 * Directories to fetch translations from.
	 * @var array
	 */
	protected $directories;

	public function __construct(\Symfony\Component\Translation\TranslatorInterface $translator, $directories=null) {
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
		$dst = $this->input->getArgument('dst');
		$dstLocale = $this->input->getArgument('dstLocale');

		$e = new Extractor;
		foreach($this->directories as $dir)
			$e->parseDirectory($dir);

		$res = $e->getList($this->translator, $dstLocale);

		if(!$res)
			$this->comment('No translations to export.');
		else {
			$dumper = new \Symfony\Component\Yaml\Dumper();
			$yaml = $dumper->dump($res, 1);
			file_put_contents($dst, $yaml);

			$this->info('Translations exported with success.');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['dstLocale', InputArgument::REQUIRED, 'Destination locale.'],
			['dst', InputArgument::REQUIRED, 'Destination file.'],
		];
	}
}