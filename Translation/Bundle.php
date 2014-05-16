<?php
namespace Asgard\Translation;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		parent::load($bundlesManager);
	}

	public function run() {
		\Asgard\Core\App::get('translator')->importLocales('locales');
	}
}