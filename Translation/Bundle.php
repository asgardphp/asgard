<?php
namespace Asgard\Translation;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		$this->app->register('translator', function($app) {
			return new \Asgard\Translation\Translator($app['config']->get('locale'));
		});
		parent::load($bundlesManager);
	}

	public function run() {
		$this->app['translator']->importLocales('locales');
	}
}