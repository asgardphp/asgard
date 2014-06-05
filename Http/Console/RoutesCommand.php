<?php
namespace Asgard\Http\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ConsoleCommand extends \Asgard\Console\Command {
	protected $name = 'routes';
	protected $description = 'List all registered routes';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$table = $this->getHelperSet()->get('table');
		$table->setHeaders(array('Method', 'Host', 'URL', 'Controller', 'Action'));

		$routes = $this->getAsgard()['resolver']->sortRoutes()->getRoutes();
		foreach($routes as $route) {
			$cb = $route->getCallback();
			$table->addRow(array(
				$route->get('method'),
				$route->get('host'),
				'/'.$route->getRoute(),
				$route instanceof \Asgard\Http\ControllerRoute ?
					$route->getController():
					($cb instanceof \Closure ?
							'Closure':
							is_array($cb) ?
								is_object($cb[0]) ? 
									'array('.get_class($cb[0]).', '.$cb[1].')':
									'array('.$cb[0].', '.$cb[1].')'
								:
								$cb
				),
				$route instanceof \Asgard\Http\ControllerRoute ? $route->getAction():'',
			));
		}

		$table->render($output);
	}
}