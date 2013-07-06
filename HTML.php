<?php
namespace Coxis\Utils;

class HTML {
	protected $include_js = array();
	protected $include_css = array();
	protected $code_js = array();
	public $code_css = array();
	protected $code = array();
	
	protected $title = '';
	protected $description = '';
	protected $keywords = '';
	
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
	
	public function show_title() {
		echo '<title>'.htmlentities($this->title, ENT_QUOTES, "UTF-8").'</title>';
	}
	
	public function show_description() {
		if($this->description)
			echo '<meta name="description" content="'.str_replace('"', '\"', $this->description).'">';
	}
	
	public function show_keywords() {
		if($this->keywords)
			echo '<meta name="keywords" content="'.str_replace('"', '\"', $this->keywords).'">';
	}
	
	public function include_js($js) {
		if(!in_array($js, $this->include_js))
			$this->include_js[] = $js;
	}
	
	public function include_css($css) {
		if(!in_array($css, $this->include_css))
			$this->include_css[] = $css;
	}
	
	public function code_js($js) {
		$this->code_js[] = $js;
	}
	
	public function code_css($css) {
		$this->code_css[] = $css;
	}
	
	public function code($code) {
		$this->code[] = $code;
	}
	
	public function show_include_js() {
		foreach($this->include_js as $js)
			echo '<script type="text/javascript" src="'.\URL::to($js).'"></script>'."\n";
	}
	
	public function show_include_css() {
		foreach($this->include_css as $css)
			echo '<link rel="stylesheet" href="'.\URL::to($css).'"/>'."\n";
	}
	
	public function show_code_js() {
		if(sizeof($this->code_js)>0) {
			echo '<script type="text/javascript">
			//<![CDATA[
			';
			foreach($this->code_js as $code)
				echo $code."\n";
			echo '//]]>
			</script>';
		}
	}
	
	public function show_code_css() {
		if(sizeof($this->code_css)>0) {
			echo '<style type="text/css">';
			foreach($this->code_css as $code)
				echo $code."\n";
			echo '</style>';
		}
	}
	
	public function show_code() {
		foreach($this->code as $code)
			echo $code."\n";
	}
	
	public function show_all() {
		$this->show_include_js();
		$this->show_include_css();
		$this->show_code_js();
		$this->show_code_css();
		$this->show_code();
	}

	public function minify_js() {
		$files = '';
		foreach($this->include_js as $js) {
			if(preg_match('/^http:/', $js))
				echo '<script type="text/javascript" src="'.$js.'"></script>';
			else
				$files .= ($files ? ',':'').$js;
		}
		if($files)
			echo '<script type="text/javascript" src="'.\URL::to('min/index.php?f='.$files).'"></script>';
		return;
	}

	public function minify_css() {
		$files = '';
		foreach($this->include_css as $css) {
			if(preg_match('/^http:/', $css))
				echo '<link rel="stylesheet" href="'.$css.'"/>';
			else
				$files .= ($files ? ',':'').$css;
		}
		if($files)
			echo '<link rel="stylesheet" href="'.\URL::to('min/index.php?f='.$files).'"/>';
		return;
	}
	
	static public function sanitize($html) {
		return htmlentities($html, ENT_NOQUOTES, 'UTF-8');
		// return htmlentities($html);
	}
}
?>