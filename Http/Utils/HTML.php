<?php
namespace Asgard\Http\Utils;

/**
 * HTML helper.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class HTML implements HTMLInterface {
	/**
	 * HTTP Kernel.
	 * @var \Asgard\Http\HttpKernel
	 */
	protected $httpKernel;
	/**
	 * JS files.
	 * @var array
	 */
	protected $include_js = [];
	/**
	 * CSS files.
	 * @var array
	 */
	protected $include_css = [];
	/**
	 * JS code.
	 * @var array
	 */
	protected $code_js = [];
	/**
	 * CSS code.
	 * @var array
	 */
	protected $code_css = [];
	/**
	 * Code.
	 * @var array
	 */
	protected $code = [];
	/**
	 * Title.
	 * @var string
	 */
	protected $title = '';
	/**
	 * Description.
	 * @var string
	 */
	protected $description = '';
	/**
	 * Keywords.
	 * @var string
	 */
	protected $keywords = '';
	/**
	 * Options.
	 * @var array
	 */
	protected $options = [];

	/**
	 * Constructor.
	 * @param \Asgard\Http\HttpKernel $httpKernel
	 */
	public function __construct(\Asgard\Http\HttpKernel $httpKernel) {
		$this->httpKernel = $httpKernel;
	}

	/**
	 * {@inheritDoc}
	 */
	public function codeStart() {
		ob_start();
	}

	/**
	 * {@inheritDoc}
	 */
	public function codeEnd() {
		$r = ob_get_contents();
		ob_end_clean();
		$this->code($r);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getKeywords() {
		return $this->keywords;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}

	/**
	 * {@inheritDoc}
	 */
	public function printTitle() {
		echo '<title>'.htmlentities($this->title, ENT_QUOTES, "UTF-8").'</title>';
	}

	/**
	 * {@inheritDoc}
	 */
	public function printDescription() {
		if($this->description)
			echo '<meta name="description" content="'.str_replace('"', '\"', $this->description).'">';
	}

	/**
	 * {@inheritDoc}
	 */
	public function printKeywords() {
		if($this->keywords)
			echo '<meta name="keywords" content="'.str_replace('"', '\"', $this->keywords).'">';
	}

	/**
	 * {@inheritDoc}
	 */
	public function includeJS($js) {
		if(!in_array($js, $this->include_js))
			$this->include_js[] = $js;
	}

	/**
	 * {@inheritDoc}
	 */
	public function includeCSS($css) {
		if(!in_array($css, $this->include_css))
			$this->include_css[] = $css;
	}

	/**
	 * {@inheritDoc}
	 */
	public function codeJS($js) {
		$this->code_js[] = $js;
	}

	/**
	 * {@inheritDoc}
	 */
	public function codeCSS($css) {
		$this->code_css[] = $css;
	}

	/**
	 * {@inheritDoc}
	 */
	public function code($code) {
		$this->code[] = $code;
	}

	/**
	 * {@inheritDoc}
	 */
	public function printJSInclude() {
		foreach($this->include_js as $js) {
			if(preg_match('/^(?:\/|\\\\|\w:\\\\|\w:\/).*$/', $js) || preg_match('/^[a-zA-Z0-9]*:\/\//', $js))#absolute
				echo '<script type="text/javascript" src="'.$js.'"></script>'."\n";
			else
				echo '<script type="text/javascript" src="'.$this->getRequest()->url->to($js).'"></script>'."\n";
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function printCSSInclude() {
		foreach($this->include_css as $css) {
			if(preg_match('/^(?:\/|\\\\|\w:\\\\|\w:\/).*$/', $css) || preg_match('/^[a-zA-Z0-9]*:\/\//', $css))#absolute
				echo '<link rel="stylesheet" href="'.$css.'"/>'."\n";
			else
				echo '<link rel="stylesheet" href="'.$this->getRequest()->url->to($css).'"/>'."\n";
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function printJSCode() {
		if(count($this->code_js)>0) {
			echo '<script type="text/javascript">
			//<![CDATA[
			';
			foreach($this->code_js as $code)
				echo $code."\n";
			echo '//]]>
			</script>';
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function printCSSCode() {
		if(count($this->code_css)>0) {
			echo '<style type="text/css">';
			foreach($this->code_css as $code)
				echo $code."\n";
			echo '</style>';
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function printCode() {
		foreach($this->code as $code)
			echo $code."\n";
	}

	/**
	 * {@inheritDoc}
	 */
	public function printAll() {
		$this->printJSInclude();
		$this->printCSSInclude();
		$this->printJSCode();
		$this->printCSSCode();
		$this->printCode();
	}

	/**
	 * Sanitize HTML input.
	 * @param  string $html
	 * @return string
	 */
	public static function sanitize($html) {
		return htmlentities($html, ENT_NOQUOTES, 'UTF-8');
	}

	/**
	 * Set an option.
	 * @param string $name
	 * @param mixed  $value
	 */
	public function set($name, $value) {
		$this->options[$name] = $value;
		return $this;
	}

	/**
	 * Get an option.
	 * @param  string $name
	 * @return mixed
	 */
	public function get($name) {
		if(!isset($this->options[$name]))
			return;
		return $this->options[$name];
	}

	public function getRequest() {
		return $this->httpKernel->getRequest();
	}
}