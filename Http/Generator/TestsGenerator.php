<?php
namespace Asgard\Http\Generator;

class TestsGenerator {
	use \Asgard\Container\ContainerAwareTrait;

	public function __construct($container) {
		$this->container = $container;
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
		\Asgard\File\FileSystem::write($dst, $res);
	}

	protected function doGenerateTests(&$count) {
		$root = $this->container['kernel']['root'];

		exec('phpunit', $res);

		if(!is_array($res))
			return false;
		if(strpos(implode("\n", $res), 'No tests executed') === false) {
			if(strpos(implode("\n", $res), 'OK (') === false)
				return false;
		}

		if(file_exists($root.'/tests/tested.txt'))
			$tested = array_filter(explode("\n", file_get_contents($root.'/tests/tested.txt')));
		else
			$tested = [];
		if(file_exists($root.'/tests/ignore.txt'))
			$tested = array_merge(array_filter(explode("\n", file_get_contents($root.'/tests/ignore.txt'))));
		\Asgard\File\FileSystem::delete($root.'/tests/tested.txt');

		$routes = $this->container['resolver']->getRoutes();

		$res = [];
		foreach($routes as $route) {
			foreach($tested as $url) {
				if($this->container['resolver']->matchWith($route->getRoute(), $url) !== false)
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
			[],
			[],
		)->isOK(), \''.$route->getRoute().'\');
		*/
		';
			}
		}

		$count = count($res);

		return implode('', $res);
	}

	protected function createTestFile($dst) {
		\Asgard\File\FileSystem::write($dst, '<?php
class '.explode('.', basename($dst))[0].' extends \Asgard\Http\Test {
	public function test() {
	}
}');
	}
}
