<?php
class Migration2 extends \Asgard\Migration\Migration {
	public static $migrated = false;

	public function up() {
		static::$migrated = true;
	}

	public function down() {
	}
}