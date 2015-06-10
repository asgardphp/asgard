<?php
namespace Asgard\Translation;

class Translation {
	protected $translator;
	protected $dirs = [];

	public function __construct(\Symfony\Component\Translation\Translator $translator) {
		$this->translator = $translator;
	}

	public function addDir($dir) {
		$this->dirs[] = $dir;

		foreach(glob($dir.'/'.$this->translator->getLocale().'/*') as $file)
			$this->translator->addResource('yaml', $file, $this->translator->getLocale());
	}

	public function load($locale) {
		foreach($this->dirs as $dir) {
			foreach(glob($dir.'/'.$locale.'/*') as $file)
				$this->translator->addResource('yaml', $file, $locale);
		}
	}

	public function getTranslator() {
		return $this->translator;
	}
}