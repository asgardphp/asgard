<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Maintenance up command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class UpCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'up';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Bring the application out of maintenance mode';

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		\Asgard\File\FileSystem::delete($this->getContainer()['kernel']['root'].'/storage/maintenance');
		$this->info('The application is now up.');
	}
}