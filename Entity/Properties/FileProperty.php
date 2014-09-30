<?php
namespace Asgard\Entity\Properties;

/**
 * File Property.
 */
class FileProperty extends \Asgard\Entity\Property {
	/**
	 * Default allowed extensions.
	 * @var array
	 */
	protected static $defaultExtensions = ['pdf', 'doc', 'jpg', 'jpeg', 'png', 'docx', 'gif', 'rtf', 'ppt', 'xls', 'zip', 'txt'];

	/**
	 * {@inheritDoc}
	 */
	public function __construct($params) {
		$params['extensions'] = static::$defaultExtensions;
		parent::__construct($params);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRules() {
		$rules = parent::getRules();
		$rules['isNull'] = function($input) {
			return !$input || $input->shouldDelete() || !$input->src();
		};
		if(!isset($rules['extension']))
			$rules['extension'] = $this->get('extensions');

		return $rules;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSQLType() {
		return 'varchar(255)';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _getDefault() {
		return null;
	}

	protected function doSerialize($obj) {
		if(is_object($obj))
			return $obj->src();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doUnserialize($str) {
		if(!$str || !file_exists($str))
			return null;
		$file = new \Asgard\Entity\File($str);
		$container = $this->definition->getContainer();
		if($container->has('config') && isset($container['config']['webdir']))
			$file->setWebDir($container['config']['webdir']);
		if($container->has('httpKernel'))
			$file->setUrl($container['httpKernel']->getRequest()->url);
		$file->setDir($this->get('dir'));
		$file->setWeb($this->get('web'));
		return $file;
	}

	/**
	 * {@inheritDoc}
	 */
	public function doSet($val, \Asgard\Entity\Entity $entity, $name) {
		if(is_string($val) && $val !== null)
			$val = new \Asgard\Entity\File($val);
		if(is_object($val)) {
			if($val instanceof \Asgard\Http\HttpFile)
				$val = new \Asgard\Entity\File($val->src(), $val->getName());
			$container = $this->definition->getContainer();
			if($container->has('config') && isset($container['config']['webdir']))
				$val->setWebDir($container['config']['webdir']);
			if($container->has('httpKernel'))
				$val->setUrl($container['httpKernel']->getRequest()->url);
			$val->setDir($this->get('dir'));
			$val->setWeb($this->get('web'));
		}
		return $val;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFormField() {
		return 'Asgard\Form\Fields\FileField';
	}
}