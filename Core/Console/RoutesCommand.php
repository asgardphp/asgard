<?php
namespace Asgard\Core\Console;

class RoutesCommand extends \Asgard\Console\Command {
	protected $name = 'routes';
	protected $description = 'List all registered routes';

	protected function execute() {
		$table = $this->getHelperSet()->get('table');
		$table->setHeaders(['Method', 'Host', 'URL', 'Controller', 'Action']);

		$routes = $this->getAsgard()['resolver']->sortRoutes()->getRoutes();
		foreach($routes as $route) {
			$cb = $route->getCallback();
			$table->addRow([
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
			]);
		}

		$table->render($this->output);
	}
}