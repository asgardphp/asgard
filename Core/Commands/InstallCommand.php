<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class InstallCommand extends \Asgard\Console\Command {
	protected $name = 'install';
	protected $description = 'Install a module into your application';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$sources = $this->input->getArgument('sources');
		$suggest = $this->input->getOption('suggest');
		$migrate = $this->input->getOption('migrate');
		$updateComposer = $this->input->getOption('update-composer');
		$root = $this->getContainer()['kernel']['root'];
		if(file_exists($root.'/modules.json'))
			$modules = json_decode(file_get_contents($root.'/modules.json'), true);
		else
			$modules = [];
		if(file_exists($root.'/composer.json'))
			$appComposer = json_decode(file_get_contents($root.'/composer.json'), true);
		else
			$appComposer = null;

		foreach($sources as $src)
			$this->install($src, $suggest, $migrate, $updateComposer, $root, $modules, $appComposer);

		if($updateComposer && $appComposer) {
			file_put_contents($root.'/composer.json', json_encode($appComposer, JSON_PRETTY_PRINT));
			$this->updateComposer($root);
		}
	}

	protected function install($src, $suggest, $migrate, $updateComposer, $root, $modules, $appComposer) {
		$tmp = sys_get_temp_dir().'/'.\Asgard\Common\Tools::randstr(10);

		if(!$this->gitInstall($src, $tmp)) {
			$this->error('The files could not be downloaded.');
			return;
		}

		if(!file_exists($tmp.'/asgard.json')) {
			$this->error('asgard.json is missing for '.$src.'.');
			return;
		}

		foreach(glob($tmp.'/app/*') as $dir) {
			$dir = basename($dir);
			if(file_exists($root.'/app/'.$dir)) {
				$this->error('Some of the app files already exists for '.$src.'.');
				return;
			}
		}

		foreach(glob($tmp.'/migrations/*') as $dir) {
			$dir = basename($dir);
			if(file_exists($root.'/migrations/'.$dir)) {
				$this->error('Some of the migration files already exists for '.$src.'.');
				return;
			}
		}

		if(file_exists($tmp.'/asgard.json'))
			$asgard = json_decode(file_get_contents($tmp.'/asgard.json'), true);
		else
			$asgard = [];
		if(!isset($asgard['name'])) {
			$this->error('Name missing for '.$src.'.');
			return;
		}
		else
			$name = $asgard['name'];

		if(in_array($name, $modules)) {
			$this->comment($name.' has already been installed.');
			return;
		}

		#asgard deps
		if(isset($asgard['require'])) {
			foreach($asgard['require'] as $requireName=>$requireSrc) {
				if(!in_array($requireName, $modules))
					$this->install($requireSrc, $suggest, $migrate, $updateComposer, $root, $modules, $appComposer);
			}
		}
		if($suggest && isset($asgard['suggest'])) {
			foreach($asgard['suggest'] as $requireName=>$requireSrc) {
				if(!in_array($requireName, $modules))
					$this->install($requireSrc, $suggest, $migrate, $updateComposer, $root, $modules, $appComposer);
			}
		}

		$publisher = new Publisher($this->getContainer());

		#copy app
		if(file_exists($tmp.'/app')) {
			if(!$publisher->publish($tmp.'/app', $root.'/app'))
				$this->warning('app/ could not be published.');
		}

		#copy config
		if(file_exists($tmp.'/config')) {
			if(!$publisher->publish($tmp.'/config', $root.'/config'))
				$this->warning('config/ could not be published.');
		}

		#copy tests
		if(file_exists($tmp.'/tests')) {
			if(!$publisher->publish($tmp.'/tests', $root.'/tests'))
				$this->warning('tests/ could not be published.');
		}

		#copy web
		if(file_exists($tmp.'/web')) {
			if(!$publisher->publish($tmp.'/web', $root.'/web'))
				$this->warning('web/ could not be published.');
		}

		#copy migrations
		if(file_exists($tmp.'/migrations/migrations.json')) {
			if(!$publisher->publishMigrations($tmp.'/migrations', $root.'/migrations', $migrate))
				$this->warning('migrations/ could not be published.');
		}

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
		file_put_contents($root.'/modules.json', json_encode(array_unique($modules), JSON_PRETTY_PRINT));

		$this->info('Module "'.$name.'" installed with success.');
	}

	protected function gitInstall($src, $tmp) {
		$cmd = 'git clone "'.$src.'" "'.$tmp.'"';
		return $this->runCommand($cmd);
	}

	protected function updateComposer($dir) {
		$cmd = 'composer update --working-dir "'.$dir.'"';
		return $this->runCommand($cmd);
	}

	protected function runCommand($cmd) {
		$this->comment($cmd);

		$process = proc_open($cmd,
			[
			   0 => ['pipe', 'r'],
			   1 => ['pipe', 'w'],
			   2 => ['pipe', 'w'],
			],
			$pipes
		);

		return proc_close($process) === 0;
	}

	protected function getOptions() {
		return [
			['suggest', null, InputOption::VALUE_NONE, 'Install suggested dependencies.', null],
			['migrate', null, InputOption::VALUE_NONE, 'Automatically execute the migrations.', null],
			['update-composer', null, InputOption::VALUE_NONE, 'Automatically updates composer.', null],
		];
	}

	protected function getArguments() {
		return [
			['sources', InputArgument::IS_ARRAY, 'Source folder'],
		];
	}
}