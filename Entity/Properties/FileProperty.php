<?php
namespace Asgard\Entity\Properties;

/**
 * File Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class FileProperty extends \Asgard\Entity\Property {
	/**
	 * Default allowed extensions.
	 * @var array
	 */
	protected static $defaultExtensions = ['pdf', 'doc', 'jpg', 'jpeg', 'png', 'docx', 'gif', 'rtf', 'ppt', 'xls', 'zip', 'txt'];
	/**
	 * Web directory.
	 * @var string
	 */
	protected $webDir;
	/**
	 * URL dependency.
	 * @var \Asgard\Http\URLInterface
	 */
	protected $url;

	/**
	 * Set the web directory.
	 * @param string $webDir
	 */
	public function setWebDir($webDir) {
		$this->webDir = $webDir;
	}

	/**
	 * Set the URL dependency.
	 * @param \Asgard\Http\URLInterface $url
	 */
	public function setUrl(\Asgard\Http\URLInterface $url) {
		$this->url = $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function __construct($params) {
		if(!isset($params['extensions']))
			$params['extensions'] = static::$defaultExtensions;
		parent::__construct($params);
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
		parent::prepareValidator($validator);
		$rules['isNull'] = function($input) {
			return !$input || $input->shouldDelete() || !$input->src();
		};
		if(!isset($rules['extension']))
			$rules['extension'] = $this->get('extensions');
		$validator->rules($rules);
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

	/**
	 * {@inheritDoc}
	 */
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
		$file->setWebDir($this->webDir);
		$file->setUrl($this->url);
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
			$val->setWebDir($this->webDir);
			$val->setUrl($this->url);
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