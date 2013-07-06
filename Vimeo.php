<?php
class Vimeo {
	public $url;

	function __construct($url) {
		$this->url = $url;
	}

	public function create($url) {
		return new Vimeo($url);
	}

	public function getEmbed() {
		if($this->getType() == 'vimeo')
			return '<iframe src="http://player.vimeo.com/video/'.$this->getID().'?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff" width="220" style="float:left; margin:0 10px 10px 0;" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
	}
		
	// public function getPreview() {
	// 	if($this->getType() == 'youtube')
	// 		return 'http://img.youtube.com/vi/'.$this->getID().'/0.jpg';
	// }
	
	// public function getURL() {
	// 	if($this->getType() == 'youtube')
	// 		return 'http://www.youtube.com/watch?v='.$this->getID();
	// }
	
	public function getType() {
		return 'vimeo';
	}
	
	public function getID() {
		if($this->getType() == 'vimeo')
			if(preg_match('/http:\/\/vimeo.com\/([0-9]+)/', $this->url, $matches))
				return $matches[1];
			// elseif(preg_match('/youtu.be\/(.+)/', $this->url, $matches))
			// 	return $matches[1];
	}
}