<?php
class Youtube {
	public $url;

	function __construct($url) {
		$this->url = $url;
	}

	public function create($url) {
		return new Youtube($url);
	}

	public function getEmbed() {
		if($this->getType() == 'youtube')
			return '<iframe class="youtube-player" style="float:left; margin:0 10px 10px 0;" frameborder="0" src="http://www.youtube.com/embed/'.$this->getID().'" title="YouTube video player" type="text/html" width="220" height=""></iframe>';
	}
		
	public function getPreview() {
		if($this->getType() == 'youtube')
			return 'http://img.youtube.com/vi/'.$this->getID().'/0.jpg';
	}
	
	public function getURL() {
		if($this->getType() == 'youtube')
			return 'http://www.youtube.com/watch?v='.$this->getID();
	}
	
	public function getType() {
		if(preg_match('/youtube.com/', $this->url, $matches))
			return 'youtube';
		elseif(preg_match('/youtu.be/', $this->url, $matches))
			return 'youtube';
		return null;
	}
	
	public function getID() {
		if($this->getType() == 'youtube')
			if(preg_match('/v=([^&]*)/', $this->url, $matches))
				return $matches[1];
			elseif(preg_match('/youtu.be\/(.+)/', $this->url, $matches))
				return $matches[1];
	}
}