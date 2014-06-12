<?php
namespace Asgard\Migration;

class Migration {
	protected $app;

	public function __construct($app) {
		$this->app = $app;
	}

	public function _up() {
		$this->up();
	}

	public function _down() {
		$this->down();
	}
}