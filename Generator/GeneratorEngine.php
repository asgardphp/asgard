<?php
namespace Asgard\Generator;

/**
 * Generator.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class GeneratorEngine implements GeneratorEngineInterface {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Flag to override existing files.
	 * @var boolean
	 */
	protected $overrideFiles = false;
	/**
	 * Generators;
	 * @var array
	 */
	protected $generators = [];

	protected $appRoot;

	/**
	 * Constructor.
	 * @param \Asgard\Container\ContainerInterface $container
	 * @param string                               $appRoot
	 */
	public function __construct(\Asgard\Container\ContainerInterface $container, $appRoot) {
		$this->container = $container;
		$this->appRoot = $appRoot;
	}

	/**
	 * Set flag to override existing files.
	 * @param boolean $overrideFiles
	 */
	public function setOverrideFiles($overrideFiles) {
		$this->overrideFiles = $overrideFiles;
	}

	/**
	 * Return flag to override existing files.
	 * @return boolean $overrideFiles
	 */
	public function getOverrideFiles() {
		return $this->overrideFiles;
	}

	/**
	 * Process a template file.
	 * @param string $src
	 * @param string $dst
	 * @param array  $vars
	 */
	public function processFile($src, $dst, $vars) {
		if(!$this->overrideFiles && file_exists($dst))
			return;
		$container = $this->container;

		$_src = $src;
		$_dst = $dst;
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
	 * @param  mixed   $v
	 * @param  integer $tabs
	 * @param  boolean $line
	 * @return string
	 */
	public function outputPHP($v, $tabs=0, $line=false) {
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
		else
			return $r.var_export($v, true);
	}

	public function addGenerator(AbstractGenerator $generator) {
		$generator->setEngine($this);
		$this->generators[] = $generator;
	}

	public function generate(array $bundles, $root) {
		foreach($bundles as $name=>&$bundle) {
			$bundle['name'] = $name;
			foreach($this->generators as $generator)
				$generator->preGenerate($bundle);
		}

		foreach($bundles as $name=>$bundle) {
			$dst = $this->appRoot.'/'.ucfirst(strtolower($name)).'/';
			foreach($this->generators as $generator)
				$generator->generate($bundle, $root, $dst);
		}

		foreach($bundles as $name=>$bundle) {
			$dst = $this->appRoot.'/'.ucfirst(strtolower($name)).'/';
			foreach($this->generators as $generator)
				$generator->postGenerate($bundle, $root, $dst);
		}
	}
}