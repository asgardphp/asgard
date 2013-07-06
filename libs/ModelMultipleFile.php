<?php
namespace Coxis\Files\Libs;

class ModelMultipleFile {
	public $model;
	public $name;
	public $property;
	public $files = array();
	public $saved = true;
	
	function __construct($model, $name, $files=array()) {
		if(!$model::hasProperty($name))
			throw new \Exception('File '.$name.' does not exist for model '.get_class($model));

		if(!is_array($files))
			$files = array();
		$this->name = $name;
		$this->model = $model;
		$this->property = $model::property($name);
		foreach($files as $file)
			$this->files[] = new ModelFile($model, $name, $file);
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
			$this->files[] = new ModelFile($this->model, $this->name, $file);
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

		$this->model->save(null, true);
		
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