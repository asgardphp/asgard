<?php
class Migration extends \Asgard\Migration\Migration {
	public static $migrated = false;

	public function up() {
		static::$migrated = true;
	}

	public function down() {
	}
}