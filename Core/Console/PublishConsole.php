<?php
namespace Asgard\Core\Console;

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
		$root = $this->getAsgard()['kernel']['root'];

		$publisher = new Publisher();

		#copy app
		if($publishApp && file_exists($bundle.'/app')) {
			$publisher->publish($bundle.'/app', $root.'/app');
			$output->writeln('<info>App files have been published.</info');
		}

		#copy config
		if($publishConfig && file_exists($bundle.'/config')) {
			$publisher->publish($bundle.'/config', $root.'/config');
			$output->writeln('<info>App files have been published.</info');
		}

		#copy tests
		if($publishTests && file_exists($bundle.'/Tests')) {
			$publisher->publish($bundle.'/Tests', $root.'/Tests');
			$output->writeln('<info>App files have been published.</info');
		}

		#copy web
		if($publishWeb && file_exists($bundle.'/web')) {
			$publisher->publish($bundle.'/web', $root.'/web');
			$output->writeln('<info>App files have been published.</info');
		}

		#copy migrations
		if($publishMigrations && file_exists($bundle.'/Migrations/migrations.json')) {
			$publisher->publishMigrations($bundle.'/Migrations', $root.'/Migrations', $migrate);
			$output->writeln('<info>App files have been published.</info');
		}
	}

	protected function getOptions() {
		return array(
			array('all', null, InputOption::VALUE_NONE, 'Publish all files.', null),
			array('app', null, InputOption::VALUE_NONE, 'Publish app files.', null),
			array('tests', null, InputOption::VALUE_NONE, 'Publish test files.', null),
			array('web', null, InputOption::VALUE_NONE, 'Publish web files.', null),
			array('migrations', null, InputOption::VALUE_NONE, 'Publish migrations.', null),
			array('config', null, InputOption::VALUE_NONE, 'Publish config files.', null),
			array('migrate', null, InputOption::VALUE_NONE, 'Automatically execute the migrations.', null),
		);
	}

	protected function getArguments() {
		return array(
			array('bundle', InputArgument::REQUIRED, 'Path to bundle'),
		);
	}
}