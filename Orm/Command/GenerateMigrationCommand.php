<?php
namespace Asgard\Orm\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generate migration command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class GenerateMigrationCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'orm:generate';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Generate a migration from ORM entities';
	/**
	 * Entities manager dependency.
	 * @var \Asgard\Entity\EntityManagerInterface
	 */
	protected $entityManager;
	/**
	 * Migrations manager dependency.
	 * @var \Asgard\Migration\MigrationManagerInterface
	 */
	protected $MigrationManager;
	/**
	 * DataMapper dependency.
	 * @var \Asgard\Orm\DataMapperInterface
	 */
	protected $dataMapper;

	/**
	 * Constructor.
	 * @param \Asgard\Entity\EntityManagerInterface      $entityManager
	 * @param \Asgard\Migration\MigrationManagerInterface $MigrationManager
	 * @param \Asgard\Orm\DataMapperInterface              $dataMapper
	 */
	public function __construct(\Asgard\Entity\EntityManagerInterface $entityManager, \Asgard\Migration\MigrationManagerInterface $MigrationManager, \Asgard\Orm\DataMapperInterface $dataMapper) {
		$this->entityManager = $entityManager;
		$this->MigrationManager = $MigrationManager;
		$this->dataMapper = $dataMapper;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $this->input->getArgument('migration') ? $this->input->getArgument('migration'):'Migration';

		$dm = $this->dataMapper;
		$mm = $this->MigrationManager;
		$om = new \Asgard\Orm\ORMMigrations($dm, $mm);

		$definitions = [];
		foreach($this->entityManager->getDefinitions() as $definition) {
			if($definition->get('ormMigrate'))
				$definitions[] = $definition;
		}
		$migration = $om->generateMigration($definitions, $migration);

		if($migration === null)
			$this->comment('There is nothing to migrate.');
		elseif($mm->has($migration))
			$this->info('The migration was successfully generated.');
		else
			$this->error('The migration could not be generated.');
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