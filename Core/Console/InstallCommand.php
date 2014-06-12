<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class InstallCommand extends \Asgard\Console\Command {
	protected $name = 'install';
	protected $description = 'Install a module into your application';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$sources = $input->getArgument('sources');
		$migrate = $input->getOption('migrate');
		$updateComposer = $this->input->getOption('update-composer');
		$root = $this->getAsgard()['kernel']['root'];
		if(file_exists($root.'/modules.json'))
			$modules = json_decode(file_get_contents($root.'/modules.json'), true);
		else
			$modules = [];
		if(file_exists($root.'/composer.json'))
			$appComposer = json_decode(file_get_contents($root.'/composer.json'), true);
		else
			$appComposer = null;

		foreach($sources as $src)
			$this->install($src, $migrate, $updateComposer, $root, $modules, $appComposer);

		if($updateComposer && $appComposer) {
			file_put_contents($root.'/composer.json', json_encode($appComposer, JSON_PRETTY_PRINT));
			$this->updateComposer($root);
		}
	}

	protected function install($src, $migrate, $updateComposer, $root, $modules, $appComposer) {
		$tmp = sys_get_temp_dir().'/'.\Asgard\Common\Tools::randStr(10);

		if(!$this->gitInstall($src, $tmp)) {
			$this->output->writeln('<error>The files could not be downloaded.</error>');
			return;
		}

		if(!file_exists($tmp.'/asgard.json')) {
			$this->output->write('<error>asgard.json is missing for '.$src.'.</error>');
			continue;
		}

		foreach(glob($tmp.'/app/*') as $dir) {
			$dir = basename($dir);
			if(file_exists($root.'/app/'.$dir)) {
				$this->output->write('<error>Some of the app files already exists for '.$src.'.</error>');
				continue 2;
			}
		}

		foreach(glob($tmp.'/Migrations/*') as $dir) {
			$dir = basename($dir);
			if(file_exists($root.'/Migrations/'.$dir)) {
				$this->output->write('<error>Some of the migration files already exists for '.$src.'.</error>');
				continue 2;
			}
		}

		if(file_exists($tmp.'/asgard.json'))
			$asgard = json_decode(file_get_contents($tmp.'/asgard.json'), true);
		else
			$asgard = [];
		if(!isset($asgard['name'])) {
			$this->output->write('<error>Name missing for '.$src.'.</error>');
			continue;
		}
		else
			$name = $asgard['name'];

		if(in_array($name, $modules)) {
			$this->output->write($name.' has already been installed.');
			continue;
		}

		#asgard deps
		if(isset($asgard['require'])) {
			foreach($asgard['require'] as $requireName=>$requireSrc) {
				if(!in_array($requireName, $modules))
					$this->install($requireSrc, $migrate, $updateComposer, $root, $modules, $appComposer);
			}
		}

		$publisher = new Publisher();

		#copy app
		if(file_exists($tmp.'/app'))
			$publisher->publish($tmp.'/app', $root.'/app');

		#copy config
		if(file_exists($tmp.'/config'))
			$publisher->publish($tmp.'/config', $root.'/config');

		#copy tests
		if(file_exists($tmp.'/Tests'))
			$publisher->publish($tmp.'/Tests', $root.'/Tests');

		#copy web
		if(file_exists($tmp.'/web'))
			$publisher->publish($tmp.'/web', $root.'/web');

		#copy migrations
		if(file_exists($tmp.'/Migrations/migrations.json'))
			$publisher->publishMigrations($tmp.'/Migrations', $root.'/Migrations', $migrate);

		#composer
		if($updateComposer && $appComposer && file_exists($tmp.'/composer.json')) {
			$modComposer = json_decode(file_get_contents($tmp.'/composer.json'), true);
			if(isset($modComposer['require']))
				$appComposer['require'] = array_merge($modComposer['require'], $appComposer['require']);
			if(isset($modComposer['autoload']))
				$appComposer['autoload'] = array_merge_recursive($modComposer['autoload'], $appComposer['autoload']);
			$version = isset($asgard['version']) ? $asgard['version']:'dev-master';
			$appComposer['replace'][$name] = $version;
		}
		
		#scripts
		if(isset($asgard['scripts'])) {
			foreach($asgard['scripts'] as $script)
				include $tmp.'/'.$script;
		}

		$modules[] = $name;
		file_put_contents($root.'/modules.json', json_encode($modules, JSON_PRETTY_PRINT));

		$this->output->writeln('<info>Module "'.$name.'" installed with success.</info>');
	}

	protected function getOptions() {
		return [
			['migrate', null, InputOption::VALUE_NONE, 'Automatically execute the migrations.', null],
			['update-composer', null, InputOption::VALUE_NONE, 'Automatically updates composer.', null],
		];
	}

	protected function getArguments() {
		return [
			['sources', InputArgument::IS_ARRAY, 'Source folder'],
		];
	}

	protected function gitInstall($src, $tmp) {
		$cmd = 'git clone '.$src.' '.$tmp;

		$process = proc_open($cmd,
			[
			   0 => ["pipe", "r"],
			   1 => ["pipe", "w"],
			   2 => ["pipe", "w"],
			],
			$pipes
		);

		return proc_close($process) === 0;
	}

	protected function updateComposer($dir) {
		$cmd = 'composer update --working-dir '.$dir;

		$process = proc_open($cmd,
			[
			   0 => ["pipe", "r"],
			   1 => ["pipe", "w"],
			   2 => ["pipe", "w"],
			],
			$pipes
		);

		return proc_close($process) === 0;
	}
}