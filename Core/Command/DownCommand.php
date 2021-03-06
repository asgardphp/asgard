<?php
namespace Asgard\Core\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Maintenance down command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DownCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'down';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Put the application into maintenance mode';

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		\Asgard\File\FileSystem::write($this->getContainer()['kernel']['root'].'/storage/maintenance', '');
		$this->info('The application is now down');
	}
}