<?php
namespace Coxis\Files\Libs;

class FileProperty extends \Coxis\Core\Properties\BaseProperty {
	public static $defaultallowed = array('pdf', 'doc', 'jpg', 'jpeg', 'png', 'docx', 'gif', 'rtf', 'ppt', 'xls', 'zip', 'txt');

	public function getRules() {
		$rules = parent::getRules();
		if(isset($rules['required'])) {
			$rules['filerequired'] = $rules['required'];
			unset($rules['required']);
		}
		unset($rules['dir']);
		unset($rules['multiple']);
		if(!isset($rules['allowed']))
			$rules['allowed'] = static::$defaultallowed;

		return $rules;
	}

	public function getSQLType() {
		return 'varchar(255)';
	}

	public function getDefault($model=null) {
		if($this->multiple)
			return new \Coxis\Files\Libs\ModelMultipleFile($model, $this->name, array());
		else
			return new \Coxis\Files\Libs\ModelFile($model, $this->name, null);
	}

	public function serialize($obj) {
		if($this->multiple)
			return serialize($obj->getNames());
		else
			return $obj->file;
	}

	public function unserialize($str, $model=null) {
		if($this->multiple)
			try {
				return new \Coxis\Files\Libs\ModelMultipleFile($model, $this->name, unserialize($str));
			} catch(\Exception $e) {
				return $this->getDefault($model);
			}
		return new \Coxis\Files\Libs\ModelFile($model, $this->name, $str);
	}

	public function set($val, $model=null) {
		if(is_object($val))
			return $val;

		if($this->multiple)
			return new \Coxis\Files\Libs\ModelMultipleFile($model, $this->name, $val);
		else
			return new \Coxis\Files\Libs\ModelFile($model, $this->name, $val);
	}
}