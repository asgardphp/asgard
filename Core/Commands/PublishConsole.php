<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class PublishCommand extends \Asgard\Console\Command {
	protected $name = 'publish';
	protected $description = 'Publish a bundle files';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$bundle = $this->input->getArgument('bundle');

		$publishAll = $this->input->getOption('all');
		$publishApp = $publishAll || $this->input->getOption('app');
		$publishMigrations = $publishAll || $this->input->getOption('migrations');
		$publishTests = $publishAll || $this->input->getOption('tests');
		$publishConfig = $publishAll || $this->input->getOption('config');
		$publishWeb = $publishAll || $this->input->getOption('web');

		$migrate = $this->input->getOption('migrate');
		$root = $this->getContainer()['kernel']['root'];

		$publisher = new Publisher();

		#copy app
		if($publishApp && file_exists($bundle.'/app')) {
			$publisher->publish($bundle.'/app', $root.'/app');
			$this->info('App files have been published.');
		}

		#copy config
		if($publishConfig && file_exists($bundle.'/config')) {
			$publisher->publish($bundle.'/config', $root.'/config');
			$this->info('Config files have been published.');
		}

		#copy tests
		if($publishTests && file_exists($bundle.'/tests')) {
			$publisher->publish($bundle.'/tests', $root.'/tests');
			$this->info('Test files have been published.');
		}

		#copy web
		if($publishWeb && file_exists($bundle.'/web')) {
			$publisher->publish($bundle.'/web', $root.'/web');
			$this->info('Web files have been published.');
		}

		#copy migrations
		if($publishMigrations && file_exists($bundle.'/migrations/migrations.json')) {
			$publisher->publishMigrations($bundle.'/migrations', $root.'/migrations', $migrate);
			$this->info('Migration files have been published.');
		}
	}

	protected function getOptions() {
		return [
			['all', null, InputOption::VALUE_NONE, 'Publish all files.', null],
			['app', null, InputOption::VALUE_NONE, 'Publish app files.', null],
			['tests', null, InputOption::VALUE_NONE, 'Publish test files.', null],
			['web', null, InputOption::VALUE_NONE, 'Publish web files.', null],
			['migrations', null, InputOption::VALUE_NONE, 'Publish migrations.', null],
			['config', null, InputOption::VALUE_NONE, 'Publish config files.', null],
			['migrate', null, InputOption::VALUE_NONE, 'Automatically execute the migrations.', null],
		];
	}

	protected function getArguments() {
		return [
			['bundle', InputArgument::REQUIRED, 'Path to bundle'],
		];
	}
}