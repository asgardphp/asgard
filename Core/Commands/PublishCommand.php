<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Asgard\Core\Publisher;

/**
 * Publish command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class PublishCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'publish';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Publish a bundle files';
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
	public function __construct(\Asgard\Db\DBInterface $db, \Asgard\Db\SchemaInterface $schema) {
		$this->db = $db;
		$this->schema = $schema;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
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

		$publisher = new Publisher($this->db, $this->schema, $this->output, $this->getContainer());

		#copy app
		if($publishApp && file_exists($bundle.'/app')) {
			if($publisher->publish($bundle.'/app', $root.'/app'))
				$this->info('App files have been published.');
			else
				$this->comment('App files could not be published.');
		}

		#copy tests
		if($publishTests && file_exists($bundle.'/tests')) {
			if($publisher->publish($bundle.'/tests', $root.'/tests'))
				$this->info('Test files have been published.');
			else
				$this->comment('Test files could not be published.');
		}

		#copy config
		if($publishConfig && file_exists($bundle.'/config')) {
			if($publisher->publish($bundle.'/config', $root.'/config'))
				$this->info('Config files have been published.');
			else
				$this->comment('Config files could not be published.');
		}

		#copy web
		if($publishWeb && file_exists($bundle.'/web')) {
			if($publisher->publish($bundle.'/web', $root.'/web'))
				$this->info('Web files have been published.');
			else
				$this->comment('Web files could not be published.');
		}

		#copy migrations
		if($publishMigrations && file_exists($bundle.'/migrations/migrations.json')) {
			if($publisher->publishMigrations($bundle.'/migrations', $root.'/migrations', $migrate))
				$this->info('Migration files have been published.');
			else
				$this->comment('Migration files could not be published.');
		}

		$this->info('Files published with success.');
	}

	/**
	 * {@inheritDoc}
	 */
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

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['bundle', InputArgument::REQUIRED, 'Path to bundle'],
		];
	}
}