<?php
namespace Asgard\Entity;

class Image extends File {
	protected $format;
	protected $quality;

	public function format() {
		$format = $this->format;
		if(!$format) {
			$format = explode('.', $this->getName())[count(explode('.', $this->getName()))-1];
			if($format == 'jpeg')
				$format = 'jpg';
		}
		switch($format) {
			case 'gif':
				return IMAGETYPE_GIF;
			case 'jpg':
				return IMAGETYPE_JPEG;
			case 'png':
				return IMAGETYPE_PNG;
		}
		throw new \Exception('Format '.$format.' is invalid.');
	}

	public function setFormat($format) {
		$this->format = $format;
	}

	public function setQuality($quality) {
		$this->quality = $quality;
	}

	public function move($dst, $rename=true) {
		if($this->isAt($dst)) return;
		if($this->isUploaded()) {
			$format = $this->format();
			$dst = preg_replace('/\.[^\.]+$/', '', $dst);
			$dst .= '.'.$format;
			$this->src = \Asgard\Common\ImageManager::load($this->src)->save($dst, $format, $this->quality, $rename);

			return $this->src;
		}

		return parent::move($dst, $rename);
	}
}