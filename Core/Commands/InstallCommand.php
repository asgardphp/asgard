<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Asgard\Core\Publisher;

/**
 * Install a module command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class InstallCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'install';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Install a module into your application';
	/**
	 * Db.
	 * @var \Asgard\Db\DBInterface
	 */
	protected $db;
	/**
	 * Schema.
	 * @var \Asgard\Db\SchemaInterface
	 */
	protected $schema;

	/**
	 * Constructor.
	 * @param \Asgard\Db\DBInterface                            $db
	 * @param \Asgard\Db\SchemaInterface                        $schema
	 */
	public function __construct(\Asgard\Db\DBInterface $db=null, \Asgard\Db\SchemaInterface $schema=null) {
		$this->db = $db;
		$this->schema = $schema;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
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

	/**
	 * Install a module.
	 * @param  string $src
	 * @param  boolean $suggest
	 * @param  boolean $migrate
	 * @param  boolean $updateComposer
	 * @param  string $root
	 * @param  array $modules
	 * @param  array $containerComposer
	 */
	protected function install($src, $suggest, $migrate, $updateComposer, $root, &$modules, &$containerComposer) {
		$tmp = $root.'/tmp/'.\Asgard\Common\Tools::randstr(10);

		list($src, $tag) = explode('=', $src.'=');

		if(!$this->gitInstall($src, $tmp) || ($tag && !$this->gitCheckout($tmp, $tag))) {
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

		$publisher = new Publisher($this->db, $this->schema, $this->output, $this->getContainer());

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

	/**
	 * Git clone a project.
	 * @param  string $src
	 * @param  string $tmp
	 * @return boolean
	 */
	protected function gitInstall($src, $tmp) {
		$cmd = 'git clone --recursive "'.$src.'" "'.$tmp.'"';
		return $this->runCommand($cmd);
	}

	/**
	 * Git checkout.
	 * @param  string $dir
	 * @param  string $tag
	 * @return boolean
	 */
	protected function gitCheckout($dir, $tag) {
		$cmd = 'cd "'.$dir.'" & git checkout tags/'.$tag;
		return $this->runCommand($cmd);
	}

	/**
	 * Update composer.
	 * @param  string $dir
	 * @return boolean
	 */
	protected function updateComposer($dir) {
		$cmd = 'composer update --working-dir "'.$dir.'"';
		return $this->runCommand($cmd);
	}

	/**
	 * Run a command.
	 * @param  string  $cmd
	 * @return boolean
	 */
	protected function runCommand($cmd) {
		$this->comment($cmd);

		passthru($cmd, $return);
		return $return === 0;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getOptions() {
		return [
			['suggest', null, InputOption::VALUE_NONE, 'Install suggested dependencies.', null],
			['migrate', null, InputOption::VALUE_NONE, 'Automatically execute the migrations.', null],
			['update-composer', null, InputOption::VALUE_NONE, 'Automatically updates composer.', null]
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['sources', InputArgument::IS_ARRAY, 'Source folder'],
		];
	}
}