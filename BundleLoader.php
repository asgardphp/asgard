<?php
namespace Coxis\Core;

class BundleLoader {
	protected $bundle = null;

	#for auto-loader only
	public function load($queue) {
	}

	public function run() {
		$bundle = $this->getBundle();

		\Context::get('locale')->importLocales($bundle.'/locales');

		#todo only load classes if no cache
		#todo if preload enabled only
		Autoloader::preloadDir($bundle.'/models');
		Autoloader::preloadDir($bundle.'/libs');

		if(file_exists($bundle.'/hooks/')) {
			Autoloader::preloadDir($bundle.'/hooks');
			foreach(glob($bundle.'/hooks/*.php') as $filename)
				\Coxis\Core\Importer::loadClassFile($filename);
		}

		if(file_exists($bundle.'/controllers/')) {
			Autoloader::preloadDir($bundle.'/controllers');
			foreach(glob($bundle.'/controllers/*.php') as $filename)
				\Coxis\Core\Importer::loadClassFile($filename);
		}

		if(file_exists($bundle.'/cli/')) {
			Autoloader::preloadDir($bundle.'/cli');
			foreach(glob($bundle.'/cli/*.php') as $filename)
				\Coxis\Core\Importer::loadClassFile($filename);
		}
	}

	public function setBundle($bundle) {
		$this->bundle = $bundle;
	}

	public function getBundle() {
		if($this->bundle !== null)
			return $this->bundle;

		$reflector = new \ReflectionClass(get_called_class());
		return dirname($reflector->getFileName());
	}
}