<?php
class Migration3 extends \Asgard\Migration\Migration {
	public static $unmigrated = false;

	public function up() {
	}

	public function down() {
		static::$unmigrated = true;
	}
}