<?php
namespace Asgard\Migration;

abstract class DBMigration extends Migration {
	public function _up() {
		if($this->app['db'])
			$this->app['db']->beginTransaction();
		try {
			parent::_up();
			if($this->app['db'])
				$this->app['db']->commit();
		} catch(\Exception $e) {
			if($this->app['db'])
				$this->app['db']->rollback();
			throw $e;
		}
	}

	public function _down() {
		if($this->app['db'])
			$this->app['db']->beginTransaction();
		try {
			parent::_down();
			if($this->app['db'])
				$this->app['db']->commit();
		} catch(\Exception $e) {
			if($this->app['db'])
				$this->app['db']->rollback();
			throw $e;
		}
	}
}