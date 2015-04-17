<?php
namespace Asgard\Tester;

class TestsGenerator extends \Asgard\Generator\AbstractGenerator {
	protected $testBuilder;
	protected $resolver;
	protected $controllersAnnotationReader;

	public function __construct(\Asgard\Tester\TestBuilderInterface $testBuilder, \Asgard\Http\ResolverInterface $resolver, \Asgard\Http\AnnotationReader $controllersAnnotationReader) {
		$this->testBuilder = $testBuilder;
		$this->resolver = $resolver;
		$this->controllersAnnotationReader = $controllersAnnotationReader;
	}

	public function preGenerate(array &$bundle) {
		if(!isset($bundle['tests']))
			$bundle['tests'] = false;
		else
			$bundle['tests'] = true;
	}

	public function postGenerate(array $bundle, $root, $bundlePath) {
		if($bundle['tests'] === false)
			return;

		$tests = [];

		foreach($bundle['entities'] as $name=>$entity) {
			if($entity['front']) {
				$class = $bundle['namespace'].'\\Controllers\\'.ucfirst($entity['meta']['name']).'Controller';
				$routes = $this->controllersAnnotationReader->fetchRoutes($class);
				$this->resolver->addRoutes($routes);

				if(in_array('index', $entity['front']) || isset($entity['front']['index'])) {
					$route = $this->resolver->getRouteFor([$class, 'index']);
					$indexRoute = $route->getRoute();
					$testName = str_replace('\\', '', $route->getController()).ucfirst($route->getAction());
					$tests[] = [
						'test' => '
public function test'.$testName.'() {
	$browser = $this->createBrowser();
	$this->assertTrue($browser->get(\''.$indexRoute.'\')->isOK(), \'GET '.$indexRoute.'\');
}',
						'routes' => $route,
						'commented' => strpos($indexRoute, ':') !== false
					];
				}
				if(in_array('show', $entity['front']) || isset($entity['front']['show'])) {
					$route = $this->resolver->getRouteFor([$class, 'show']);
					$showRoute = $route->getRoute();
					$testName = str_replace('\\', '', $route->getController()).ucfirst($route->getAction());
					$tests[] = [
						'test' => '
public function test'.$testName.'() {
	$browser = $this->createBrowser();
	$this->assertTrue($browser->get(\''.$showRoute.'\')->isOK(), \'GET '.$showRoute.'\');
}',
						'routes' => $route,
						'commented' => strpos($showRoute, ':') !== false
					];
				}
			}
		}

		foreach($bundle['controllers'] as $name=>$controller) {
			$class = $bundle['namespace'].'\\Controllers\\'.ucfirst($controller['name']);
			$routes = $this->controllersAnnotationReader->fetchRoutes($class);
			$this->resolver->addRoutes($routes);

			foreach($controller['actions'] as $action=>$params) {
				try {
					$route = $this->resolver->getRouteFor([$class, 'index']);
					$actionRoute = $route->getRoute();
				} catch(\Exception $e) {
					continue;
				}
				$testName = str_replace('\\', '', $route->getController()).ucfirst($route->getAction());
				$tests[] = [
					'test' => '
public function test'.$testName.'() {
	$browser = $this->createBrowser();
	$this->assertTrue($browser->get(\''.$actionRoute.'\')->isOK(), \'GET '.$actionRoute.'\');
}',
					'routes' => $route,
					'commented' => strpos($actionRoute, ':') !== false
				];
			}
		}

		$this->testBuilder->buildTests($tests, $bundle['name']);
	}
}