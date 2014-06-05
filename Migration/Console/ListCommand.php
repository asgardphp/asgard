<?php
namespace Asgard\Migration\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ListCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:list';
	protected $description = 'Displays the list of migrations';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$table = $this->getHelperSet()->get('table');
		$table->setHeaders(array('Name', 'Status', 'Migrated', 'Added'));

		$tracker = new \Asgard\Migration\Tracker($this->getAsgard()['kernel']['root'].'/migrations/');
		foreach($tracker->getList() as $migration=>$params)
			$table->addRow(array($migration, isset($params['migrated']) ? 'up':'down', isset($params['migrated']) ? date('d/m/Y H:i:s', $params['migrated']):'', date('d/m/Y H:i:s', $params['added'])));

		$table->render($output);
	}
}