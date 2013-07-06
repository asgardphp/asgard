<?php
namespace Coxis\Files\Libs;

class ModelFile {
	public $model;
	public $property;
	public $file;
	public $saved = true;
	
	function __construct($model, $name, $file=array()) {
		if(!$model::hasProperty($name))
			throw new \Exception('File '.$name.' does not exist for model '.get_class($model));

		$this->model = $model;
		$this->property = $model::property($name);
		if(is_array($file)) {
			if(!$file['name'])
				return;
			$this->saved = false;
		}
		$this->file = $file;
	}

	public function extension() {
		if(is_array($this->file))
			$name = $this->file['name'];
		else
			$name = $this->get();
		if(!$name)
			return;
	
		return pathinfo($name, PATHINFO_EXTENSION);
	}

	public function notAllowed() {
		if(!$this->exists())
			return false;
		$property = $this->property;
		
		if(!in_array($this->extension(), $property::$defaultallowed))
			return $this->extension();
		return false;
	}

	public function allowed() {
		return !$this->notAllowed();
	}
	
	public function exists() {
		if(!$path = $this->get(null, true))
			return false;
		return file_exists($path);
	}
	
	public function dir($absolute=false) {
		$dir = $this->property->dir;
		$dir = trim($dir, '/');
		if($absolute)
			$dir = 'web/upload/'.$dir.($dir ? '/':'');
		else
			$dir = 'upload/'.$dir.($dir ? '/':'');
		return $dir;
	}
	
	public function get($default=null, $absolute=false) {
		$file = $this->file;
		if(is_array($file))
			return $file['path'];
		elseif($file)
			return $this->dir($absolute).$file;
		else
			return $default;
	}
	
	public function url($default=null) {
		return \URL::to($this->get($default, false));
	}
	
	public function save() {
		if(!is_array($file = $this->file))
			return;
		$to = $this->dir(true).$file['name'];
		if($this->type() == 'image') {
			if(!($format = $this->format()))
				$format = IMAGETYPE_JPEG;
			$filename = \Coxis\Utils\ImageManager::load($file['path'])->save($to, $format);
		}
		else
			$filename = \Coxis\Utils\FileManager::move($file['path'], $to);
		
		$this->file = $filename;
		$this->saved = true;

		return $this;
	}
	
	public function delete() {
		if($path = $this->get()) {
			\Coxis\Utils\FileManager::unlink(_WEB_DIR_.$path);
			\Coxis\Imagecache\Libs\ImageCache::clearFile($path);
		}
		$this->file = null;
		$this->model->save(null, true);
		
		return $this;
	}
	
	public function __toString() {
		return (string)$this->url();
	}
	
	public function property() {
		return $this->property;
	}
	
	public function required() {
		return isset($this->property->required) && $this->property->required;
	}
	
	public function type() {
		return $this->property->filetype;
	}
	
	public function format() {
		return $this->property->format;
	}
	
	public function multiple() {
		return $this->property->multiple;
	}
}