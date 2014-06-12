<?php
namespace Asgard\Http\Utils;

class HTML {
	protected $request;

	protected $include_js = [];
	protected $include_css = [];
	protected $code_js = [];
	protected $code_css = [];
	protected $code = [];
	
	protected $title = '';
	protected $description = '';
	protected $keywords = '';
	
	public function __construct($request) {
		$this->request = $request;
	}

	public function codeStart() {
		ob_start();
	}
	
	public function codeEnd() {
		$r = ob_get_contents();
		ob_end_clean();
		$this->code($r);
	}

	public function getTitle() {
		return $this->title;
	}
	public function getDescription() {
		return $this->description;
	}
	public function getKeywords() {
		return $this->keywords;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function setDescription($description) {
		$this->description = $description;
	}
	
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}
	
	public function printTitle() {
		echo '<title>'.htmlentities($this->title, ENT_QUOTES, "UTF-8").'</title>';
	}
	
	public function printDescription() {
		if($this->description)
			echo '<meta name="description" content="'.str_replace('"', '\"', $this->description).'">';
	}
	
	public function printKeywords() {
		if($this->keywords)
			echo '<meta name="keywords" content="'.str_replace('"', '\"', $this->keywords).'">';
	}
	
	public function includeJS($js) {
		if(!in_array($js, $this->include_js))
			$this->include_js[] = $js;
	}
	
	public function includeCSS($css) {
		if(!in_array($css, $this->include_css))
			$this->include_css[] = $css;
	}
	
	public function codeJS($js) {
		$this->code_js[] = $js;
	}
	
	public function codeCSS($css) {
		$this->code_css[] = $css;
	}
	
	public function code($code) {
		$this->code[] = $code;
	}
	
	public function printJSInclude() {
		foreach($this->include_js as $js) {
			if(preg_match('/http:\/\//', $js))
				echo '<script type="text/javascript" src="'.$js.'"></script>'."\n";
			else
				echo '<script type="text/javascript" src="'.$this->request->url->to($js).'"></script>'."\n";
		}
	}
	
	public function printCSSInclude() {
		foreach($this->include_css as $css) {
			if(preg_match('/http:\/\//', $css))
				echo '<link rel="stylesheet" href="'.$css.'"/>'."\n";
			else
				echo '<link rel="stylesheet" href="'.$this->request->url->to($css).'"/>'."\n";
		}
	}
	
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
	
	public function printCSSCode() {
		if(count($this->code_css)>0) {
			echo '<style type="text/css">';
			foreach($this->code_css as $code)
				echo $code."\n";
			echo '</style>';
		}
	}
	
	public function printCode() {
		foreach($this->code as $code)
			echo $code."\n";
	}
	
	public function printAll() {
		$this->printJSInclude();
		$this->printCSSInclude();
		$this->printJSCode();
		$this->printCSSCode();
		$this->printCode();
	}

	public function minifyJS() {
		$files = '';
		foreach($this->include_js as $js) {
			if(preg_match('/^http:/', $js))
				echo '<script type="text/javascript" src="'.$js.'"></script>';
			else
				$files .= ($files ? ',':'').$js;
		}
		if($files)
			echo '<script type="text/javascript" src="'.$this->request->url->to('min/index.php?f='.$files).'"></script>';
		return;
	}

	public function minifyCSS() {
		$files = '';
		foreach($this->include_css as $css) {
			if(preg_match('/^http:/', $css))
				echo '<link rel="stylesheet" href="'.$css.'"/>';
			else
				$files .= ($files ? ',':'').$css;
		}
		if($files)
			echo '<link rel="stylesheet" href="'.$this->request->url->to('min/index.php?f='.$files).'"/>';
		return;
	}
	
	static public function sanitize($html) {
		return htmlentities($html, ENT_NOQUOTES, 'UTF-8');
	}
}
?>