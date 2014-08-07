<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Asgard\Core\Publisher;

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
			$containerComposer = json_decode(file_get_contents($root.'/composer.json'), true);
		else
			$containerComposer = null;

		foreach($sources as $src)
			$this->install($src, $suggest, $migrate, $updateComposer, $root, $modules, $containerComposer);

		file_put_contents($root.'/modules.json', json_encode(array_unique($modules), JSON_PRETTY_PRINT));

		if($updateComposer && $containerComposer) {
			file_put_contents($root.'/composer.json', json_encode($containerComposer, JSON_PRETTY_PRINT));
			$this->updateComposer($root);
		}
	}

	protected function install($src, $suggest, $migrate, $updateComposer, $root, &$modules, &$containerComposer) {
		$tmp = sys_get_temp_dir().'/'.\Asgard\Common\Tools::randstr(10);

		if(!$this->gitInstall($src, $tmp)) {
			$this->error('The files could not be downloaded.');
			return;
		}

		if(!file_exists($tmp.'/asgard.json')) {
			$this->error('asgard.json is missing for '.$src.'.');
			return;
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
					$this->install($requireSrc, $suggest, $migrate, $updateComposer, $root, $modules, $containerComposer);
			}
		}
		if($suggest && isset($asgard['suggest'])) {
			foreach($asgard['suggest'] as $requireName=>$requireSrc) {
				if(!in_array($requireName, $modules))
					$this->install($requireSrc, $suggest, $migrate, $updateComposer, $root, $modules, $containerComposer);
			}
		}

		$publisher = new Publisher($this->getContainer(), $this->output);

		$publisher->publish($tmp.'/app', $root.'/app');
		$publisher->publish($tmp.'/config', $root.'/config');
		$publisher->publish($tmp.'/tests', $root.'/tests');
		$publisher->publish($tmp.'/web', $root.'/web');
		$publisher->publishMigrations($tmp.'/migrations', $root.'/migrations', $migrate);

		#composer
		if($updateComposer && $containerComposer && file_exists($tmp.'/composer.json')) {
			$modComposer = json_decode(file_get_contents($tmp.'/composer.json'), true);
			if(isset($modComposer['require']))
				$containerComposer['require'] = array_merge($modComposer['require'], $containerComposer['require']);
			if(isset($modComposer['autoload']))
				$containerComposer['autoload'] = array_merge_recursive($modComposer['autoload'], $containerComposer['autoload']);
			$version = isset($asgard['version']) ? $asgard['version']:'@dev';
			$containerComposer['replace'][$name] = $version;
		}
		
		#scripts
		if(isset($asgard['scripts'])) {
			foreach($asgard['scripts'] as $script)
				include $tmp.'/'.$script;
		}

		$modules[] = $name;

		$this->info('Module "'.$name.'" added with success.');
	}

	protected function gitInstall($src, $tmp) {
		$cmd = 'git clone --recursive "'.$src.'" "'.$tmp.'"';
		return $this->runCommand($cmd, true);
	}

	protected function updateComposer($dir) {
		$cmd = 'composer update --working-dir "'.$dir.'"';
		return $this->runCommand($cmd, true);
	}

	protected function runCommand($cmd, $verbose=false) {
		$this->comment($cmd);

		$process = proc_open($cmd,
			$pipes = [
			   0 => ['pipe', 'r'],
			   1 => ['pipe', 'w'],
			   2 => ['pipe', 'w'],
			],
			$pipes
		);

		if($verbose) {
			echo stream_get_contents($pipes[1]);
			fclose($pipes[1]);
		}

		while(($status=proc_get_status($process)) && $status['running']) {}
		$exitCode = $status['exitcode'];
		proc_close($process);
		return $exitCode === 0;
	}

	protected function getOptions() {
		return [
			['suggest', null, InputOption::VALUE_NONE, 'Install suggested dependencies.', null],
			['migrate', null, InputOption::VALUE_NONE, 'Automatically execute the migrations.', null],
			['update-composer', null, InputOption::VALUE_NONE, 'Automatically updates composer.', null]
		];
	}

	protected function getArguments() {
		return [
			['sources', InputArgument::IS_ARRAY, 'Source folder'],
		];
	}
}