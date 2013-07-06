<?php
namespace Coxis\Form;

class DynamicGroup extends \Coxis\Form\AbstractGroup {
	protected $cb;
	protected $default_render;

	function __construct($cb, $default_render=null) {
		$this->cb = $cb;
		$this->default_render = $default_render;
	}

	public function setDefaultRender($default_render) {
		$this->default_render = $default_render;
	}

	public function setData($data, $files) {
		$this->data = array_values($data);
		$this->files = array_values($files);

		#merge data/files everywhere in form?
		coxis_array_merge($data, $files);
		
		foreach($data as $k=>$v) 
			$this->newField($k, $v);
		
		$this->updateChilds();

		return $this;
	}

	public function newField($name=null, $data=null) {
		// return;
		if($name !== null && isset($this[$name]))
			return;
		$cb = $this->cb;
		$newelement = $cb($data);
		if(!$newelement)
			return;
		$this->addField($newelement, $name);
		return $newelement;
	}

	public function def($field=null) {
		$default_render = $this->default_render;
		if($default_render === null)
			throw new \Exception('No default render callback for this DynamicGroup');

		// if(!isset($this[$offset]))
		// 	$this->newField($offset);
		// $field = $this[$offset];

		// $r = $default_render($this[$offset]);
		$r = $default_render($field);
		return $r;
	}

	public function renderNew($offset=null) {
		$default_render = $this->default_render;
		if($default_render === null)
			throw new \Exception('No default render callback for this DynamicGroup');

		if($offset === null)
			$offset = $this->size();

		if(!isset($this[$offset]))
			$field = $this->newField($offset);
		else
			$field = $this[$offset];
		if(!$field)
			return;

		$r = $default_render($field);
		unset($this[$offset]);
		return $r;
	}

	public function renderjQuery($offset) {
		$randstr = Tools::randstr(10);
		$jq = $this->renderNew('{{'.$randstr.'}}');
		$jq = addcslashes($jq, "'");
		$jq = str_replace("\r\n", "\n", $jq);
		$jq = str_replace("\n", "\\\n", $jq);
		$jq = str_replace('{{'.$randstr.'}}', $offset, $jq);
		return $jq;
	}
}