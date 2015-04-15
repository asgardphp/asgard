<?php
namespace Asgard\Core;

class TranslationResources {
	protected $directories = [];

	public function add($locale, $directory) {
		$this->directories[$locale][] = $directory;
	}

	public function get($locale) {
		if(!isset($this->directories[$locale]))
			return [];
		return $this->directories[$locale];
	}

	public function getFiles($locale) {
		$files = [];
		foreach($this->get($locale) as $dir)
			$files = array_merge($files, glob($dir.'/*'));
		return $files;
	}
}