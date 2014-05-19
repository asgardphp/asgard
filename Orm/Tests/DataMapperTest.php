<?php
namespace Asgard\Orm\Tests;

class DataMapperTest extends \PHPUnit_Framework_TestCase {
	protected static $app;

	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');

		$app = new \Asgard\Core\App;
		static::$app = $app;
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