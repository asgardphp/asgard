<?php
namespace Asgard\Files\Libs;

class EntityMultipleFile {
	public $entity;
	public $name;
	public $property;
	public $files = array();
	public $saved = true;
	
	public function __construct($entity, $name, $files=array()) {
		if(!$entity::hasProperty($name))
			throw new \Exception('File '.$name.' does not exist for Entity '.get_class($entity));

		if(!is_array($files))
			$files = array();
		$this->name = $name;
		$this->entity = $entity;
		$this->property = $entity::property($name);
		foreach($files as $file)
			$this->files[] = new EntityFile($entity, $name, $file);
	}

	public function extension() {
		$extensions = array();
		foreach($this->files as $file)
			$extensions[] = $file->extension();
		return $extensions;
	}

	public function notAllowed() {
		$extensions = array();
		foreach($this->files as $file)
			if($ext = $file->notAllowed())
				return $ext;
		return false;
	}

	public function allowed() {
		return !$this->notAllowed();
	}
	
	public function exists() {
		$extensions = array();
		foreach($this->files as $file)
			if(!$file->exists())
				return false;
		return true;
	}
	
	public function getNames() {
		$res = array();
		foreach($this->files as $file)
			if(is_string($file->file))
				$res[] = $file->file;
		return $res;
	}
	
	public function get($default=null, $absolute=false) {
		$res = array();
		foreach($this->files as $file)
			$res[] = $file->get($default, $absolute);
		return $res;	
	}
	
	public function save() {
		foreach($this->files as $file)
			$file->save();
		return $this;
	}

	public function add($files) {
		foreach($files as $file)
			$this->files[] = new EntityFile($this->entity, $this->name, $file);
		return $this;
	}
	
	public function delete($pos=null) {
		if($pos !== null) {
			$pos = (int)$pos;
			if(!isset($this->files[$pos]))
				return $this;
			$this->files[$pos]->delete();
			unset($this->files[$pos]);
		}
		else {
			foreach($this->files as $file)
				$file->delete();
			$this->files = array();
		}

		$this->entity->save(null, true);
		
		return $this;
	}

	public function toArray() {
		return $this->get();
	}
	
	public function params() {
		return $this->params;
	}
	
	public function required() {
		return isset($this->params->required) && $this->params->required;
	}
	
	public function type() {
		return $this->params->filetype;
	}
	
	public function format() {
		return $this->params->format;
	}
	
	public function multiple() {
		return $this->params->multiple;
	}
}