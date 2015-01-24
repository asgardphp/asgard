<?php
namespace Asgard\Http\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Routes command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class RoutesCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'routes';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'List all registered routes';
	/**
	 * Resolver dependency.
	 * @var \Asgard\Http\ResolverInterface
	 */
	protected $resolver;

	/**
	 * Constructor.
	 * @param \Asgard\Http\ResolverInterface $resolver
	 */
	public function __construct(\Asgard\Http\ResolverInterface $resolver) {
		$this->resolver = $resolver;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$table = new \Symfony\Component\Console\Helper\Table($this->output);
		$table->setHeaders(['Method', 'Host', 'URL', 'Controller', 'Action']);

		$routes = $this->resolver->sortRoutes()->getRoutes();
		foreach($routes as $route) {
			$action = $route->getAction();
			$table->addRow([
				$route->get('method'),
				$route->get('host'),
				'/'.$route->getRoute(),
				$route->getController(),
				($action instanceof \Closure ?
							'Closure':
							is_array($action) ?
								is_object($action[0]) ?
									'array('.get_class($action[0]).', '.$action[1].')':
									'array('.$action[0].', '.$action[1].')'
								:
								$action
				)
			]);
		}

		$table->render($this->output);
	}
}