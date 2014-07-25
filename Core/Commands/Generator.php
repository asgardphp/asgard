<?php
namespace Asgard\Core\Commands;

class Generator {
	use \Asgard\Container\ContainerAware;
	
	protected $overrideFiles = false;

	public function __construct($container) {
		$this->container = $container;
	}

	public function setOverrideFiles($overrideFiles) {
		$this->overrideFiles = $overrideFiles;
	}

	public function processFile($_src, $_dst, $vars) {
		if(!$this->overrideFiles && file_exists($_dst))
			return;
		$container = $this->container;

		foreach($vars as $k=>$v)
			$$k = $v;

		ob_start();
		include $_src;
		$content = ob_get_contents();
		ob_end_clean();

		$content = str_replace('<%', '<?php', $content);
		$content = str_replace('<%=', '<?=', $content);
		$content = str_replace('%>', '?>', $content);

		\Asgard\File\FileSystem::write($_dst, $content);
	}

	public function outputPHP($v) {
		return var_export($v, true);
	}
}