<?php
namespace Asgard\Entity\Properties;

class FileProperty extends \Asgard\Entity\Property {
	protected static $defaultExtensions = ['pdf', 'doc', 'jpg', 'jpeg', 'png', 'docx', 'gif', 'rtf', 'ppt', 'xls', 'zip', 'txt'];

	public function __construct($params) {
		$params['extensions'] = static::$defaultExtensions;
		parent::__construct($params);
	}

	public function getRules() {
		$rules = parent::getRules();
		$rules['isNull'] = function($input) {
			return !$input || $input->shouldDelete() || !$input->src();
		};
		if(!isset($rules['extension']))
			$rules['extension'] = $this->get('extensions');

		return $rules;
	}

	public function getSQLType() {
		if($this->multiple)
			return 'text';
		else
			return 'varchar(255)';
	}

	protected function _getDefault($entity=null) {
		if($this->multiple)
			return [];
		else
			return null;
	}

	protected function doSerialize($obj) {
		if(is_object($obj))
			return $obj->src();
	}

	protected function doUnserialize($str, $entity=null) {
		if(!$str || !file_exists($str))
			return null;
		$file = new \Asgard\Entity\File($str);
		$file->setWebDir($this->definition->getApp()['kernel']['webdir']);
		$file->setUrl($this->definition->getApp()['request']->url);
		$file->setDir($this->get('dir'));
		return $file;
	}

	public function doSet($val, $entity=null) {
		if(is_string($val) && $val !== null)
			$val = new \Asgard\Entity\File($val);
		if(is_object($val)) {
			if($val instanceof \Asgard\Form\HttpFile)
				$val = new \Asgard\Entity\File($val->src(), $val->getName());
			$val->setWebDir($this->definition->getApp()['kernel']['webdir']);
			$val->setUrl($this->definition->getApp()['request']->url);
			$val->setDir($this->get('dir'));
		}
		return $val;
	}

	public function getFormField() {
		return 'Asgard\Form\Fields\FileField';
	}
}