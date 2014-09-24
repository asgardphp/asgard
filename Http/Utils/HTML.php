<?php
namespace Asgard\Http\Utils;

/**
 * HTML helper.
 */
class HTML {
	/**
	 * HTTP Request.
	 * @var \Asgard\Http\Request
	 */
	protected $request;
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
	 * Constructor.
	 * @param \Asgard\Http\Request $request
	 */
	public function __construct($request) {
		$this->request = $request;
	}

	/**
	 * Start buffer to write code.
	 */
	public function codeStart() {
		ob_start();
	}
	
	/**
	 * End buffer to write code.
	 */
	public function codeEnd() {
		$r = ob_get_contents();
		ob_end_clean();
		$this->code($r);
	}

	/**
	 * Get page title.
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Get page description.
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Get page keywords
	 * @return string
	 */
	public function getKeywords() {
		return $this->keywords;
	}
	
	/**
	 * Set page title.
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * Set page description.
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}
	
	/**
	 * Set page keywords.
	 * @param string $keywords
	 */
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}
	
	/**
	 * Print the title.
	 */
	public function printTitle() {
		echo '<title>'.htmlentities($this->title, ENT_QUOTES, "UTF-8").'</title>';
	}
	
	/**
	 * Print the description.
	 */
	public function printDescription() {
		if($this->description)
			echo '<meta name="description" content="'.str_replace('"', '\"', $this->description).'">';
	}
	
	/**
	 * Print the keywords.
	 */
	public function printKeywords() {
		if($this->keywords)
			echo '<meta name="keywords" content="'.str_replace('"', '\"', $this->keywords).'">';
	}
	
	/**
	 * Include a JS file.
	 * @param  string $js
	 */
	public function includeJS($js) {
		if(!in_array($js, $this->include_js))
			$this->include_js[] = $js;
	}
	
	/**
	 * Include a CSS file.
	 * @param  string $css
	 */
	public function includeCSS($css) {
		if(!in_array($css, $this->include_css))
			$this->include_css[] = $css;
	}
	
	/**
	 * Include JS code.
	 * @param  string $js
	 */
	public function codeJS($js) {
		$this->code_js[] = $js;
	}
	
	/**
	 * Include CSS code.
	 * @param  string $css
	 */
	public function codeCSS($css) {
		$this->code_css[] = $css;
	}
	
	/**
	 * Include code.
	 * @param  string $code
	 */
	public function code($code) {
		$this->code[] = $code;
	}
	
	/**
	 * Print JS files.
	 */
	public function printJSInclude() {
		foreach($this->include_js as $js) {
			if(preg_match('/http:\/\//', $js))
				echo '<script type="text/javascript" src="'.$js.'"></script>'."\n";
			else
				echo '<script type="text/javascript" src="'.$this->request->url->to($js).'"></script>'."\n";
		}
	}
	
	/**
	 * Print CSS files.
	 */
	public function printCSSInclude() {
		foreach($this->include_css as $css) {
			if(preg_match('/http:\/\//', $css))
				echo '<link rel="stylesheet" href="'.$css.'"/>'."\n";
			else
				echo '<link rel="stylesheet" href="'.$this->request->url->to($css).'"/>'."\n";
		}
	}
	
	/**
	 * Print JS code.
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
	 * Print CSS code.
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
	 * Pritn code.
	 */
	public function printCode() {
		foreach($this->code as $code)
			echo $code."\n";
	}
	
	/**
	 * Print all.
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
	static public function sanitize($html) {
		return htmlentities($html, ENT_NOQUOTES, 'UTF-8');
	}
}
?>