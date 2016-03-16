<?php
namespace Asgard\Cache\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clear cache command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ClearCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'cc';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Clear the application cache';
	/**
	 * Cache dependency.
	 * @var \Doctrine\Common\Cache\Cache
	 */
	protected $cache;

	/**
	 * Constructor.
	 * @param \Doctrine\Common\Cache\Cache $cache
	 */
	public function __construct(\Doctrine\Common\Cache\Cache $cache) {
		$this->cache = $cache;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		if(is_subclass_of($this->cache, 'Doctrine\Common\Cache\ClearableCache') && $this->cache->deleteAll())
			$this->info('The cache has been cleared.');
		else
			$this->error('The cache could not be cleared.');
	}
}