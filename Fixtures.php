<?php
namespace Asgard\Tester;

class Fixtures {
	protected $mm;
	protected $db;

	public function __construct($db, $mm) {
		$this->db = $db;
		$this->mm = $mm;
	}

	public function generate($fixturesFile) {
		$mm = $this->mm;
		$schema = $this->db->getSchema();
		$db = $this->db;

		$migrationName = $mm->getTracker()->getLast();

		$res = '<?php
$mm = $container[\'migrationManager\'];
$db = $container[\'db\'];
$schema = $container[\'schema\'];

$schema->dropAll();

$mm->migrateUntil(\''.$migrationName.'\');

';

		foreach($schema->listTables() as $table) {
			$name = $table->getName();
			$dal = $db->dal()->from($name);
			$res .= '$schema->emptyTable(\''.$name.'\');'."\n";
			$res .= '$dal = $db->dal()->into(\''.$name.'\');'."\n";
			while($r = $dal->next()) {
				$res .= '$dal->insert('.$this->outputPHP($r).');'."\n";
			}
			$res .= "\n";
		}

		$res .= '$mm->migrateAll();';

		\Asgard\File\FileSystem::write($fixturesFile, $res);
	}

	protected function outputPHP($v, $tabs=0, $line=false) {
		$r = '';

		if($line)
			$r .= "\n".str_repeat("\t", $tabs);

		if(is_array($v)) {
			$r .= '[';
			if($v === array_values($v)) {
				foreach($v as $_v)
					$r .= $this->outputPHP($_v, $tabs+1, true).",";
			}
			else {
				foreach($v as $_k=>$_v)
					$r .= $this->outputPHP($_k, $tabs+1, true).' => '.$this->outputPHP($_v, $tabs+1).",";
			}
			$r .= "\n".str_repeat("\t", $tabs).']';

			return $r;
		}
		else
			return $r.var_export($v, true);
	}
}