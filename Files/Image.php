<?php
namespace Asgard\Files;

class Image extends File {
	public function format() {
		return exif_imagetype($this->src);
	}

	public function move($dst, $rename=true) {
		if($this->isAt($dst)) return;
		if(!is_uploaded_file($this->src))
			return parent::move($dst, $rename);
		if($rename)
			$dst = \Asgard\Utils\FileManager::getNewFileName($dst);
		\Asgard\Utils\ImageManager::load($this->src)->save($dst, $this->format());
		$this->src = $dst;

		return $dst;
	}
}