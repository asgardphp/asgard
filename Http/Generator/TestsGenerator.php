<?php
namespace Asgard\Http\Generator;

/**
 * HTTP tests generator.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class TestsGenerator {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Constructor.
	 * @param \Asgard\Container\ContainerInterface $container
	 */
	public function __construct(\Asgard\Container\ContainerInterface $container=null) {
		$this->container = $container;
	}

	/**
	 * Generate tests.
	 * @param  string $dst destination test file.
	 * @return integer     number of generated tests.
	 */
	public function generateTests($dst) {
		$tests = $this->doGenerateTests($count);
		if($tests === false)
			return false;
		$this->addTests($tests, $dst);
		return $count;
	}

	/**
	 * Add tests to a test file.
	 * @param string $tests
	 * @param string $dst
	 */
	public function addTests($tests, $dst) {
		if(!file_exists($dst))
			$this->createTestFile($dst);

		$original = file_get_contents($dst);
		$tests = trim($tests);
		$res = preg_replace('/(\s*\})$/', "\n\t\t".$tests."\n\t".'\1', $original);
		\Asgard\File\FileSystem::write($dst, $res);
	}

	/**
	 * Actually perform the tests generation.
	 * @param  integer $count
	 * @return boolean true for success.
	 */
	protected function doGenerateTests(&$count) {
		$root = $this->container['kernel']['root'];

		if(file_exists($root.'/tests/ignore.txt')) {
			$c = trim(file_get_contents($root.'/tests/ignore.txt'), "\n")."\n";
			$ignore = array_merge(array_filter(array_map(function($a){return trim(trim($a, '\\'));}, explode("\n", $c))));
		}
		else {
			$c = '';
			$ignore = [];
		}

		$routes = $this->container['resolver']->getRoutes();

		$res = [];
		foreach($routes as $route) {
			$routeStr = $route->getController().':'.$route->getAction();
			if(in_array($routeStr, $ignore))
				continue;

			$c .= $routeStr."\n";

			$method = strtolower($route->get('method'));
			if(!$method)
				$method = 'get';

			$testName = str_replace('\\', '', $route->getController()).ucfirst($route->getAction());

			#get
			if($method === 'get' || $method === 'delete') {
				if(strpos($route->getRoute(), ':') !== false) {
					#get params
					$res[] = '
	/*
	public function test'.$testName.'() {
		$browser = $this->createBrowser();
		$this->assertTrue($browser->'.$method.'(\''.$route->getRoute().'\')->isOK(), \''.strtoupper($method).' '.$route->getRoute().'\');
	}
	*/
	';
				}
				else {
					$res[] = '
	public function test'.$testName.'() {
		$browser = $this->createBrowser();
		$this->assertTrue($browser->'.$method.'(\''.$route->getRoute().'\')->isOK(), \''.strtoupper($method).' '.$route->getRoute().'\');
	}
	';
				}
			}
			else {
				#post/put params
				$res[] = '
	/*
	public function test'.$testName.'() {
		$browser = $this->createBrowser();
		$this->assertTrue($browser->'.$method.'(\''.strtoupper($method).' '.$route->getRoute().'\',
			[],
			[],
		)->isOK(), \''.$route->getRoute().'\');
	}
	*/
	';
			}
		}

		$c = trim($c, "\n");
		\Asgard\File\FileSystem::write($root.'/tests/ignore.txt', $c);

		$count = count($res);

		return implode('', $res);
	}

	/**
	 * Create a new test file.
	 * @param  string $dst
	 */
	protected function createTestFile($dst) {
		\Asgard\File\FileSystem::write($dst, '<?php
class '.explode('.', basename($dst))[0].' extends \Asgard\Http\Test {
}');
	}
}
