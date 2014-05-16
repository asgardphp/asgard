<?php
namespace Asgard\Utils;

class ImageManager {
	var $src;
	var $rsc;
	var $width;
	var $height;
	var $type;

	public function resize(array $dim=array(), $force=false) {
		list($orig_width, $orig_height) = $this->size();
		if(isset($dim['width']) && isset($dim['height'])) {
			if($dim['width'] > $orig_width && !$force)
				$width = $orig_width;
			else
				$width = $dim['width'];
			if($dim['height'] > $orig_height && !$force)
				$height = $orig_height;
			else
				$height = $dim['height'];
		}
		elseif(isset($dim['height'])) {
			if($dim['height'] > $orig_height && !$force)
				$height = $orig_height;
			else
				$height = $dim['height'];
			$width = round($height / $orig_height * $orig_width);
		}
		elseif(isset($dim['width'])) {
			if($dim['width'] > $orig_width && !$force)
				$width = $orig_width;
			else
				$width = $dim['width'];
			$height = round($width / $orig_width * $orig_height);
		}	
		else
			return $this;
		
		$new = imagecreatetruecolor($width, $height);
		imagesavealpha($new, true);
		$trans_colour = imagecolorallocatealpha($new, 0, 0, 0, 127);
		imagefill($new, 0, 0, $trans_colour);
		$src = $this->rsc;

		imagecopyresampled($new, $src, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
		
		$this->width	=	$width;
		$this->height	=	$height;
		$this->rsc = $new;
		
		return $this;
	}

	public function crop(array $dim=array()) {
		list($orig_width, $orig_height) = $this->size();
		if(isset($dim['width']) && isset($dim['height'])) {
			if($dim['width'] > $orig_width)
				$width = $orig_width;
			else
				$width = $dim['width'];
			if($dim['height'] > $orig_height)
				$height = $orig_height;
			else
				$height = $dim['height'];
		}
		elseif(isset($dim['height'])) {
			if($dim['height'] > $orig_height)
				$height = $orig_height;
			else
				$height = $dim['height'];
			$width = $orig_width;
		}
		elseif(isset($dim['width'])) {
			if($dim['width'] > $orig_width)
				$width = $orig_width;
			else
				$width = $dim['width'];
			$width = $orig_height;
		}	
		else
			return $this;
		
		$new = imagecreatetruecolor($width, $height);
		imagesavealpha($new, true);
		$trans_colour = imagecolorallocatealpha($new, 0, 0, 0, 127);
		imagefill($new, 0, 0, $trans_colour);
		$src = $this->rsc;
		
		imagecopyresampled($new, $src, 0, 0, 0, 0, $width, $height, $width, $height);
		
		$this->width	=	$width;
		$this->height	=	$height;
		$this->rsc = $new;
		
		return $this;
	}
	
	public function size() {
		return array($this->width, $this->height);
	}
	
	public static function getImageType($file) {
		list($w, $h, $type) = getimagesize($file);
		return $type;
	}
	
	public static function load($src) {
		$img = new ImageManager();
		$img->src = $src;
		list($img->width, $img->height) = getimagesize($src);
		$img->type = $type = static::getImageType($src);
		switch($type) {
			case IMAGETYPE_GIF:
				$img->rsc = imagecreatefromgif($src);
				break;
			case IMAGETYPE_JPEG:
				$img->rsc = imagecreatefromjpeg($src);
				break;
			case IMAGETYPE_PNG:
				$img->rsc = imagecreatefrompng($src);
				imagealphablending($img->rsc, true); // setting alpha blending on
				imagesavealpha($img->rsc, true); // save alphablending setting (important)
				break;
		}
		
		return $img;
	}
	
	public function output() {
		$this->save(null);
		
		return $this;
	}
	
	public function save($dst, $type=null) {
		if($type===null)
			$type = $this->type;
						
		#only if output is not stdout
		if($dst!==null) {
			switch($type) {
				case IMAGETYPE_GIF:
					$ext = '.gif'; break;
				case IMAGETYPE_JPEG:
					$ext = '.jpg'; break;
				case IMAGETYPE_PNG:
					$ext = '.png'; break;
			}
							
			#replace file extension
			$fileexts = explode('.', $dst);
			$filename = implode('.', array_slice($fileexts, 0, -1));
			$dst = $filename.$ext;
			
			$i=1;
			while(file_exists(_DIR_.$dst))
				$dst = $filename.'_'.($i++).$ext;
		
			if($dst !=null && !file_exists(_DIR_.dirname($dst)))
				mkdir(dirname($dst), 0777, true);
		}
	
		switch($type) {
			case IMAGETYPE_GIF:
				if(imagegif($this->rsc, $dst, 100))
					return basename($dst);
				else
					return false;
			case IMAGETYPE_JPEG:
				if(imagejpeg($this->rsc, $dst, 100))
					return basename($dst);
				else
					return false;
			case IMAGETYPE_PNG:
				if(imagepng($this->rsc, $dst, 9))
					return basename($dst);
				else
					return false;
		}
	}
}