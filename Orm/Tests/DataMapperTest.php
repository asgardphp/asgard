<?php
namespace Asgard\Orm\Tests;

class DataMapperTest extends \PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
		\Asgard\Core\App::instance(true)->config->set('bundles', array(
			new \Asgard\Orm\Bundle,
			new \Asgard\Validation\Bundle,
		))->set('bundlesdirs', array());
		\Asgard\Core\App::loadDefaultApp(false);

		// \Asgard\Core\App::get('db')->import(realpath(__DIR__.'/sql/i18ntest.sql'));
	}

	public function test1() {
		// load
		// save
		// create
		// destroy
		// isNew
		// isOld
		// destroyOne
		// destroyAll
		// relation
		// getI18N
		// orm
		// getTable
		// getTranslationTable
	}
}