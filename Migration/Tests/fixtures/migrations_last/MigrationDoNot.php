<?php
class MigrationDoNot extends \Asgard\Migration\Migration {
	public static $unmigrated = false;

	public function up() {
	}

	public function down() {
		static::$unmigrated = true;
	}
}