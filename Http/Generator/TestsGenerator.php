<?php
namespace Asgard\Http\Generator;

class TestsGenerator {
	protected $app;

	public function __construct($app) {
		$this->app = $app;
	}

	public function generateTests($dst) {
		$tests = $this->doGenerateTests($count);
		if($tests === false)
			return false;
		$this->addTests($tests, $dst);
		return $count;
	}

	public function addTests($tests, $dst) {
		if(!file_exists($dst))
			$this->createTestFile($dst);
		
		$original = file_get_contents($dst);
		$tests = trim($tests);
		$res = preg_replace('/\s*(\}\s*\})$/', "\n\t\t".$tests."\n\t".'\1', $original);
		\Asgard\Utils\FileManager::put($dst, $res);
	}

	protected function doGenerateTests(&$count) {
		$root = $this->app['kernel']['root'];

		exec('phpunit', $res);

		if(strpos(implode("\n", $res), 'No tests executed') === false) {
			if(strpos(implode("\n", $res), 'OK (') === false)
				return false;
		}

		if(file_exists($root.'/Tests/tested.txt'))
			$tested = array_filter(explode("\n", file_get_contents($root.'/Tests/tested.txt')));
		else
			$tested = array();
		if(file_exists($root.'/Tests/ignore.txt'))
			$tested = array_merge(array_filter(explode("\n", file_get_contents($root.'/Tests/ignore.txt'))));
		\Asgard\Utils\FileManager::unlink($root.'/Tests/tested.txt');

		$routes = $this->app['resolver']->getRoutes();

		$res = array();
		foreach($routes as $route) {
			foreach($tested as $url) {
				if($this->app['resolver']->matchWith($route->getRoute(), $url) !== false)
					continue 2;
			}

			$method = strtolower($route->get('method'));
			if(!$method)
				$method = 'get';

			#get
			if($method === 'get' || $method === 'delete') {
				if(strpos($route->getRoute(), ':') !== false) {
					#get params
					$res[] = '
		/*
		$browser = $this->getBrowser();
		$this->assertTrue($browser->'.$method.'(\''.$route->getRoute().'\')->isOK(), \''.strtoupper($method).' '.$route->getRoute().'\');
		*/
		';
				}
				else {
					$res[] = '
		$browser = $this->getBrowser();
		$this->assertTrue($browser->'.$method.'(\''.$route->getRoute().'\')->isOK(), \''.strtoupper($method).' '.$route->getRoute().'\');
		';
				}
			}
			else {
				#post/put params
				$res[] = '
		/*
		$browser = $this->getBrowser();
		$this->assertTrue($browser->'.$method.'(\''.strtoupper($method).' '.$route->getRoute().'\',
			array(),
			array(),
		)->isOK(), \''.$route->getRoute().'\');
		*/
		';
			}
		}

		$count = count($res);

		return implode('', $res);
	}

	protected function createTestFile($dst) {
		\Asgard\Utils\FileManager::put($dst, '<?php
namespace Tests;

class '.explode('.', basename($dst))[0].' extends \Asgard\Core\Test {
	public function test() {
	}
}');
	}
}
