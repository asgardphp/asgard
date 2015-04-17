<?php
namespace Asgard\Tester;

class TestBuilder implements TestBuilderInterface {
	protected $dir;

	public function __construct($dir) {
		$this->dir = $dir;
	}

	public function getPath($name) {
		$name = ucfirst($name).'Test';
		return $this->dir.'/'.$name.'.php';
	}

	public function buildTests($tests, $name) {
		if(!$tests)
			return true;

		$name = ucfirst($name).'Test';
		$dst = $this->getPath($name);

		if(file_exists($this->dir.'/ignore.txt'))
			$c = trim(file_get_contents($this->dir.'/ignore.txt'), "\n")."\n";
		else
			$c = '';

		$res = '';
		foreach($tests as $t) {
			$test = trim($t['test']);
			$test = implode("\n\t", explode("\n", $test));
			if(isset($t['commented']) && $t['commented'])
				$res .= "\n\t/*\n\t".$test."\n\t*/\n\n";
			else
				$res .= "\t".$test."\n\n";
			if(isset($t['routes'])) {
				if(!is_array($t['routes']))
					$t['routes'] = [$t['routes']];
				foreach($t['routes'] as $route) {
					$routeStr = trim($route->getController().':'.$route->getAction(), '\\');
					$c .= $routeStr."\n";
				}
			}
		}

		$c = trim($c, "\n");
		\Asgard\File\FileSystem::write($this->dir.'/ignore.txt', $c);

		#create new test file
		if(!file_exists($dst)) {
			\Asgard\File\FileSystem::write($dst, '<?php
class '.$name.' extends \Asgard\Http\Test {
	'.trim($res).'
}', null, true);
		}
		#add to existing test file
		else {
			$c = file_get_contents($dst);
			$c = preg_replace("/\n\}\s*$/", "\n\n\t".trim($res)."\n}", $c);
			\Asgard\File\FileSystem::write($dst, $c);
		}

		return true;
	}
}