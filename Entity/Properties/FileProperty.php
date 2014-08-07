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
		if($this->get('multiple'))
			return 'text';
		else
			return 'varchar(255)';
	}

	protected function _getDefault() {
		return null;
	}

	protected function doSerialize($obj) {
		if(is_object($obj))
			return $obj->src();
	}

	protected function doUnserialize($str) {
		if(!$str || !file_exists($str))
			return null;
		$file = new \Asgard\Entity\File($str);
		$container = $this->definition->getContainer();
		if($container->has('config') && isset($container['config']['webdir']))
			$file->setWebDir($container['config']['webdir']);
		if($container->has('request'))
			$file->setUrl($container['request']->url);
		$file->setDir($this->get('dir'));
		return $file;
	}

	public function doSet($val) {
		if(is_string($val) && $val !== null)
			$val = new \Asgard\Entity\File($val);
		if(is_object($val)) {
			if($val instanceof \Asgard\Http\HttpFile)
				$val = new \Asgard\Entity\File($val->src(), $val->getName());
			$container = $this->definition->getContainer();
			if($container->has('config') && isset($container['config']['webdir']))
				$val->setWebDir($container['config']['webdir']);
			if($container->has('request'))
				$val->setUrl($container['request']->url);
			$val->setDir($this->get('dir'));
		}
		return $val;
	}

	public function getFormField() {
		return 'Asgard\Form\Fields\FileField';
	}
}