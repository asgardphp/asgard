<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Add a migration command.
 */
class AddCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'migrations:add';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Add a new migration to the list';
	/**
	 * Migrations directory.
	 * @var string
	 */
	protected $migrationsDir;

	/**
	 * Constructor.
	 * @param string $migrationsDir
	 */
	public function __construct($migrationsDir) {
		$this->migrationsDir = $migrationsDir;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$src = $this->input->getArgument('src');

		$mm = new \Asgard\Migration\MigrationsManager($this->migrationsDir);
		$migration = $mm->add($src);
		if($mm->has($migration))
			$this->info('The migration was successfully added.');
		else
			$this->error('The migration could not be added.');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['src', InputArgument::REQUIRED, 'The migration file'],
		];
	}
}