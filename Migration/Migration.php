<?php
namespace Asgard\Migration;

/**
 * Migration class
 */
class Migration {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Constructor.
	 * @param \Asgard\Container\Container $container
	 */
	public function __construct($container) {
		$this->container = $container;
	}

	/**
	 * Executed to execute a migration.
	 */
	public function up() {}

	/**
	 * Executed to rollback a migration.
	 */
	public function down() {}

	/**
	 * Wrapper for up().
	 * @return [type] [description]
	 */
	public function _up() {
		$this->up();
	}

	/**
	 * Wrapper for down().
	 * @return [type] [description]
	 */
	public function _down() {
		$this->down();
	}
}