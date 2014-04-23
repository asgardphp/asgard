<?php
namespace Asgard\Core\Cli;

class DBController extends CLIController {
	public function dumpAction($request) {
		$output = $request[0];
		echo 'Dumping data into '.$output."\n";

		\Asgard\Utils\FileManager::mkdir(dirname($output));
		$config = \Asgard\Core\App::get('config')->get('database');
		$cmd = 'mysqldump -u '.$config['user'].' '.($config['password'] ? '-p'.$config['password']:'').' '.$config['database'].' > '.$output;
		exec($cmd);
	}
	
	public function backupAction($request) {
		$request[] = 'backup/data/'.time().'.sql';
		$this->dumpAction($request);
	}
}