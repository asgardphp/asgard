<?php
namespace Asgard\Migration;

abstract class DBMigration extends Migration {
	public function _up() {
		$db = $this->app['db'];

		$db->beginTransaction();
		try {
			parent::_up();
			$db->commit();
		} catch(\Exception $e) {
			$db->rollback();
			throw $e;
		}
	}

	public function _down() {
		$db = $this->app['db'];
		
		$db->beginTransaction();
		try {
			parent::_down();
			$db->commit();
		} catch(\Exception $e) {
			$db->rollback();
			throw $e;
		}
	}
}