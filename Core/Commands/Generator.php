<?php
namespace Asgard\Core\Commands;

/**
 * Generator.
 */
class Generator {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Flag to override existing files.
	 * @var boolean
	 */
	protected $overrideFiles = false;

	/**
	 * Constructor.
	 * @param \Asgard\Container\ContainerInterface $container
	 */
	public function __construct(\Asgard\Container\ContainerInterface $container) {
		$this->container = $container;
	}

	/**
	 * Set flag to override existing files.
	 * @param boolean $overrideFiles
	 */
	public function setOverrideFiles($overrideFiles) {
		$this->overrideFiles = $overrideFiles;
	}

	/**
	 * Process a template file.
	 * @param  string $_src
	 * @param  string $_dst
	 * @param  array $vars
	 */
	public function processFile($_src, $_dst, $vars) {
		if(!$this->overrideFiles && file_exists($_dst))
			return;
		$container = $this->container;

		extract($vars);

		ob_start();
		include $_src;
		$content = ob_get_contents();
		ob_end_clean();

		$content = str_replace('<%=', '<?=', $content);
		$content = str_replace('<%', '<?php', $content);
		$content = str_replace('%>', '?>', $content);

		\Asgard\File\FileSystem::write($_dst, $content);
	}

	/**
	 * Format PHP variables to string.
	 * @param  mixed $v
	 * @return string
	 */
	public function outputPHP($v) {
		return var_export($v, true);
	}
}