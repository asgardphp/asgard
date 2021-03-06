<?php
namespace Asgard\Container\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * List available services command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ListCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'services';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Show all the services loaded in the application';
	/**
	 * Root directory.
	 * @var string
	 */
	protected $root;

	/**
	 * Constructor.
	 * @param string $root
	 */
	public function __construct($root) {
		$this->root = $root;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$table = new \Symfony\Component\Console\Helper\Table($this->output);
		$headers = ['Name', 'Type', 'Parent'];
		$optClass = $this->input->getOption('class');
		if($optClass)
			$headers[] = 'Class';
		$optRegistered = $this->input->getOption('registered');
		if($optRegistered)
			$headers[] = 'Registered at';
		$optDefined = $this->input->getOption('defined');
		if($optDefined)
			$headers[] = 'Defined in';
		$table->setHeaders($headers);

		$root = $this->root;
		$container = $this->getContainer();
		$services = [];
		foreach($container->getRegistry() as $name=>$service) {
			$defined = null;
			$class = $this->guessServiceClass($name);
			if($class !== null) {
				$r = new \ReflectionClass($class);
				$defined = \Asgard\File\FileSystem::relativeTo($root, $r->getFileName()).':'.$r->getStartLine();
			}

			if($service instanceof \SuperClosure\SerializableClosure)
				$service = $service->getClosure();

			$r = new \ReflectionFunction($service);
			$registered = \Asgard\File\FileSystem::relativeTo($root, $r->getFileName()).':'.$r->getStartLine();

			$res = [
				'name' => $name,
				'type' => 'callback',
				'parent' => $container->getParentClass($name) !== null ? $container->getParentClass($name):'???',
			];
			if($optDefined)
				$res['defined'] = $defined !== null ? $defined:'???';
			if($optRegistered)
				$res['registered'] = $registered !== null ? $registered:'???';
			if($optClass)
				$res['class'] = $class !== null ? $class:'???';
			$services[] = $res;

		}
		foreach($this->getContainer()->getInstances() as $name=>$service) {
			$defined = null;
			foreach($services as $v) {
				if($v['name'] === $name)
					continue 2;
			}

			$class = $this->guessServiceClass($name);
			if($class !== null) {
				$r = new \ReflectionClass($class);
				$defined = \Asgard\File\FileSystem::relativeTo($root, $r->getFileName()).':'.$r->getStartLine();
			}

			$res = [
				'name' => $name,
				'type' => 'instance',
				'parent' => $container->getParentClass($name) !== null ? $container->getParentClass($name):'???',
			];
			if($optDefined)
				$res['defined'] = $defined !== null ? $defined:'???';
			if($optRegistered)
				$res['registered'] = '???';
			if($optClass)
				$res['class'] = $class !== null ? $class:'???';
			$services[] = $res;
		}

		uasort($services, function($a, $b) {
			return $a['name'] > $b['name'];
		});

		foreach($services as $row)
			$table->addRow($row);

		$table->render();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getOptions() {
		return [
			['class', null, InputOption::VALUE_NONE, 'Show the returned class.', null],
			['defined', null, InputOption::VALUE_NONE, 'Show where the class is defined.', null],
			['registered', null, InputOption::VALUE_NONE, 'Show where it was registered.', null],
		];
	}

	/**
	 * Guess the class of a service.
	 * @param  string $name
	 * @return string
	 */
	protected function guessServiceClass($name) {
		try {
			$obj = $this->getContainer()->get($name);
			return gettype($obj)=='object' ? get_class($obj):gettype($obj);
		}
		catch(\Exception $e) {} #defined = ??? / class = ???
		catch(\Throwable $e) {} #defined = ??? / class = ???
	}
}