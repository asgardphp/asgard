<?php
namespace Asgard\Files\Libs;

class EntityFile {
	public $entity;
	public $property;
	public $file;
	public $saved = true;
	
	public function __construct($entity, $name, $file=array()) {
		if(!$entity::hasProperty($name))
			throw new \Exception('File '.$name.' does not exist for Entity '.get_class($entity));

		$this->entity = $entity;
		$this->property = $entity::property($name);
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

	public function allowed() {
		if(!$this->exists())
			return true;
		$property = $this->property;
		
		if(!in_array($this->extension(), $property::$defaultallowed))
			return false;
		return true;
	}

	public function notAllowed() {
		return !$this->allowed();
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
		elseif($file) {
			if(file_exists($file))
				return $file;
			else
				return $this->dir($absolute).$file;
		}
		else
			return $default;
	}
	
	public function url($default=null) {
		return \Asgard\Core\App::get('url')->to($this->get($default, false));
	}
	
	public function save() {
		if(!is_array($file = $this->file))
			return;
		$to = $this->dir(true).$file['name'];
		if($this->type() == 'image') {
			if(!($format = $this->format()))
				$format = IMAGETYPE_JPEG;
			$filename = \Asgard\Utils\ImageManager::load($file['path'])->save($to, $format);
		}
		else
			$filename = \Asgard\Utils\FileManager::move($file['path'], $to);
		
		$this->file = $filename;
		$this->saved = true;

		return $this;
	}
	
	public function delete() {
		if($path = $this->get())
			\Asgard\Utils\FileManager::unlink(_WEB_DIR_.$path);
		$this->file = null;
		
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