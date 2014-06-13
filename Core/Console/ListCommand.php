<?php
namespace Asgard\Core\Console;

class ListCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:list';
	protected $description = 'Displays the list of migrations';

	protected function execute() {
		$table = $this->getHelperSet()->get('table');
		$table->setHeaders(['Name', 'Status', 'Migrated', 'Added']);

		$tracker = new \Asgard\Migration\Tracker($this->getAsgard()['kernel']['root'].'/migrations/');
		foreach($tracker->getList() as $migration=>$params)
			$table->addRow([$migration, isset($params['migrated']) ? 'up':'down', isset($params['migrated']) ? date('d/m/Y H:i:s', $params['migrated']):'', date('d/m/Y H:i:s', $params['added'])]);

		$table->render($this->output);
	}
}