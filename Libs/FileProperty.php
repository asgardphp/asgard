<?php
namespace Asgard\Files\Libs;

class FileProperty extends \Asgard\Core\Properties\BaseProperty {
	public static $defaultextension = array('pdf', 'doc', 'jpg', 'jpeg', 'png', 'docx', 'gif', 'rtf', 'ppt', 'xls', 'zip', 'txt');

	public function getRules() {
		$rules = parent::getRules();
		if(!isset($rules['extension']))
			$rules['extension'] = static::$defaultextension;

		return $rules;
	}

	public function getSQLType() {
		return 'varchar(255)';
	}

	public function getDefault($entity=null) {
		if($this->multiple)
			return new \Asgard\Files\Libs\EntityMultipleFile($entity, $this->name, array());
		else
			return new \Asgard\Files\Libs\EntityFile($entity, $this->name, null);
	}

	public function serialize($obj) {
		if($this->multiple)
			return serialize($obj->getNames());
		else
			return $obj->file;
	}

	public function unserialize($str, $entity=null) {
		if($this->multiple)
			try {
				return new \Asgard\Files\Libs\EntityMultipleFile($entity, $this->name, unserialize($str));
			} catch(\Exception $e) {
				return $this->getDefault($entity);
			}
		return new \Asgard\Files\Libs\EntityFile($entity, $this->name, $str);
	}

	public function set($val, $entity=null) {
		if(is_object($val))
			return $val;

		if($this->multiple)
			return new \Asgard\Files\Libs\EntityMultipleFile($entity, $this->name, $val);
		elseif($val !== null)
			return new \Asgard\Files\Libs\EntityFile($entity, $this->name, $val);
	}
}