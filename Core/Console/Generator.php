<?php
namespace Asgard\Core\Console;

class Generator {
	protected $app;
	protected $overrideFiles = false;

	public function __construct($app) {
		$this->app = $app;
	}

	public function setOverrideFiles($overrideFiles) {
		$this->overrideFiles = $overrideFiles;
	}

	public function processFile($_src, $_dst, $vars) {
		if(!$this->overrideFiles && file_exists($_dst))
			return;
		$app = $this->app;

		foreach($vars as $k=>$v)
			$$k = $v;

		ob_start();
		include $_src;
		$content = ob_get_contents();
		ob_end_clean();

		$content = str_replace('<%', '<?php', $content);
		$content = str_replace('%>', '?>', $content);

		\Asgard\Utils\FileManager::put($_dst, $content);
	}
}