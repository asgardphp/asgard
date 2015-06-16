<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Deploy command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DeployCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'deploy';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Deploy the application';

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		#composer
		$this->getContainer()['errorhandler']->ignoreDir('vendor/composer');
		putenv('COMPOSER_HOME=' . $this->getContainer()['kernel']['root'] . '/vendor/bin/composer');
		$input = new ArrayInput(['command' => 'install']);
		$application = new ComposerApplication();
		$application->setAutoExit(false);
		$application->run($input);
		$this->info('Composer installed with success.');

		#cache
		try {
			$this->getContainer()['systemcache']->deleteAll();
			$this->info('Cache deleted with success.');
		} catch(\Exception $e) {}

		#compile
		$this->callSilent('compile');
		$this->info('Classes compiled with success.');

		#migrate
		$this->callSilent('migrate');
		$this->info('Migrated with success.');
	}
}