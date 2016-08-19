<?php
namespace Asgard\Orm\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generate migration command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class PreviewMigrationCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'orm:preview';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Preview a migration from ORM entities';
	/**
	 * ORM migrations manager dependency.
	 * @var \Asgard\Orm\ORMMigrations
	 */
	protected $ormMigrations;

	/**
	 * Constructor.
	 * @param \Asgard\Orm\ORMMigrations             $ormMigrations
	 */
	public function __construct(\Asgard\Orm\ORMMigrations $ormMigrations) {
		$this->ormMigrations = $ormMigrations;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $this->input->getArgument('migration') ? $this->input->getArgument('migration'):'Migration';

		$om = $this->ormMigrations;
		$mm = $om->getMigrationManager();
		$em = $om->getDataMapper()->getEntityManager();
		$entityDefinitions = $em->getDefinitions();

		$definitions = [];
		foreach($entityDefinitions as $definition) {
			if($definition->get('ormMigrate'))
				$definitions[] = $definition;
		}
		$migration = $om->previewMigration($definitions, $migration);

		if($migration === null)
			$this->comment('There is nothing to migrate.');
		else
			echo $migration;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['migration', InputArgument::OPTIONAL, 'The migration name'],
		];
	}
}