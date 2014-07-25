<?php
namespace Asgard\Migration;

class Migration {
	use \Asgard\Container\ContainerAware;

	public function __construct($container) {
		$this->container = $container;
	}

	public function up() {}
	public function down() {}

	public function _up() {
		$this->up();
	}

	public function _down() {
		$this->down();
	}
}