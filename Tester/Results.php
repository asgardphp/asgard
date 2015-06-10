<?php
namespace Asgard\Tester;

class Results {
	protected $results = [];

	public function add($id, $request, $response, $route) {
		$res = [
			'request' => $request,
			'response' => $response,
			'route' => $route,
			'coverage' => [],
		];
		$this->results[$id] = $res;

		return $this;
	}

	public function addCoverage($id, $coverage) {
		$this->results[$id]['coverage'] = new Coverage($coverage);
	}

	public function display() {
		
	}

	public function generate($name, $fixturesFile=null) {
		$tmp = [];

		foreach($this->results as $id=>$res) {
			$route = $res['route'];
			if(!$route)
				$tmp['Notfound'][''][] = $res;
			else
				$tmp[str_replace('\\', '', $route->getController())][$route->getAction()][] = $res;
		}

		#order same action's tests by coverage and request simplicity
		foreach($tmp as $controller=>$actions) {
			foreach($actions as $action=>$results) {
				usort($tmp[$controller][$action], function($a, $b) {
					if($a['coverage']->count() === $b['coverage']->count()) {
						$as = $a['request']->get->count() + $a['request']->post->count() + $a['request']->file->count() + $a['request']->cookie->count();
						$bs = $b['request']->get->count() + $b['request']->post->count() + $b['request']->file->count() + $b['request']->cookie->count();

						return $as > $bs;
					}
					return $a['coverage']->count() < $b['coverage']->count();
				});
			}
		}

		$res = '<?php
class '.$name.' extends \Asgard\Http\Test {
	public static function setUpBeforeClass() {
		$container = \Asgard\Container\Container::singleton();'.($fixturesFile ? '
		require __DIR__.\'/'.$fixturesFile.'\';':'').'
	}
';

		$coverage = new Coverage;

		foreach($tmp as $controller=>$actions) {
			foreach($actions as $action=>$results) {
				foreach($results as $k=>$test) {
					if(!$test['coverage']->hasMoreThan($coverage))
						continue;

					$request = $test['request'];
					$response = $test['response'];

					$post = $request->post->all();
					$file = $request->file->all();
					$session = $request->sessionAccessed;
					$cookies = $request->cookiesAccessed;

					$res .= "
	#Response code: ".$response->getCode()."
	public function test".$controller.ucfirst($action).($k !== 0 ? '_'.$k:'')."() {
		\$browser = \$this->createBrowser();";
		if($session)
			$res .= '
		$browser->getSession()->setAll('.$this->outputPHP($session, 2).');';
		if($cookies)
			$res .= '
		$browser->getCookies()->setAll('.$this->outputPHP($cookies, 2).');';
		$res .= "
		\$response = \$browser->req(
			'".$request->url->get().$request->url->getParams()."',
			'".$request->method()."'";
		if($post || $file)
			$res .= ', '.$this->outputPHP($post, 3);
		if($file)
			$res .= ', '.$this->outputPHP($file, 3);
		$res .= ');';
		$res .= "
		\$this->assertTrue(\$response->isOk());
	}
";

					$coverage->add($test['coverage']);
				}
			}
		}

		$res .= '}';

		\Asgard\File\FileSystem::write('tests/'.$name.'.php', $res);

		// return $res;
	}

	protected function outputPHP($v, $tabs=0, $line=false) {
		$r = '';

		if($line)
			$r .= "\n".str_repeat("\t", $tabs);

		if(is_array($v)) {
			$r .= '[';
			if($v === array_values($v)) {
				foreach($v as $_v)
					$r .= $this->outputPHP($_v, $tabs+1, true).",";
			}
			else {
				foreach($v as $_k=>$_v)
					$r .= $this->outputPHP($_k, $tabs+1, true).' => '.$this->outputPHP($_v, $tabs+1).",";
			}
			$r .= "\n".str_repeat("\t", $tabs).']';

			return $r;
		}
		elseif(is_object($v))
			return '[object] '.get_class($v);
		else
			return $r.var_export($v, true);
	}
}