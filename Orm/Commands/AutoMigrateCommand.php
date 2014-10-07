<?php
namespace Asgard\Orm\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Automigrate command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class AutoMigrateCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'orm:automigrate';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Generate and run a migration from ORM entities';
	/**
	 * Entities manager dependency.
	 * @var \Asgard\Entity\EntitiesManagerInterface
	 */
	protected $entitiesManager;
	/**
	 * Migrations manager dependency.
	 * @var \Asgard\Migration\MigrationsManagerInterface
	 */
	protected $migrationsManager;
	/**
	 * DataMapper dependency.
	 * @var \Asgard\Orm\DataMapperInterface
	 */
	protected $dataMapper;

	/**
	 * Constructor.
	 * @param \Asgard\Entity\EntitiesManagerInterface      $entitiesManager
	 * @param \Asgard\Migration\MigrationsManagerInterface $migrationsManager
	 * @param \Asgard\Orm\DataMapperInterface              $dataMapper
	 */
	public function __construct(\Asgard\Entity\EntitiesManagerInterface $entitiesManager, \Asgard\Migration\MigrationsManagerInterface $migrationsManager, \Asgard\Orm\DataMapperInterface $dataMapper) {
		$this->entitiesManager = $entitiesManager;
		$this->migrationsManager = $migrationsManager;
		$this->dataMapper = $dataMapper;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $this->input->getArgument('migration') ? $this->input->getArgument('migration'):'Automigrate';

		$dm = $this->dataMapper;
		$mm = $this->migrationsManager;
		$om = new \Asgard\Orm\ORMMigrations($dm, $mm);

		$definitions = [];
		foreach($this->entitiesManager->getDefinitions() as $definition) {
			if($definition->get('ormMigrate'))
				$definitions[] = $definition;
		}
		$migration = $om->generateMigration($definitions, $migration);
		if($mm->has($migration))
			$this->info('The migration was successfully generated.');
		else
			$this->error('The migration could not be generated.');

		if($mm->migrate($migration, true))
			$this->info('Migration succeded.');
		else
			$this->error('Migration failed.');
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