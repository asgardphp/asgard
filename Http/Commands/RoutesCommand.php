<?php
namespace Asgard\Http\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RoutesCommand extends \Asgard\Console\Command {
	protected $name = 'routes';
	protected $description = 'List all registered routes';
	protected $resolver;

	public function __construct($resolver) {
		$this->resolver = $resolver;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$table = $this->getHelperSet()->get('table');
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