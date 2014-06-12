<?php
namespace Asgard\Entity\Properties;

class ImageProperty extends FileProperty {
	protected static $defaultExtensions = ['png', 'jpg', 'jpeg', 'gif'];

	protected function doUnserialize($str, $entity=null) {
		if(!$str || !file_exists($str))
			return null;
		$image = new \Asgard\Entity\Image($str);
		$image->setWebDir($this->definition->getApp()['kernel']['webdir']);
		$image->setUrl($this->definition->getApp()['request']->url);
		$image->setFormat($this->get('format'));
		$image->setQuality($this->get('quality'));
		$image->setDir($this->get('dir'));
		return $image;
	}

	public function doSet($val, $entity=null) {
		if(is_string($val) && $val !== null)
			$val = new \Asgard\Entity\Image($val);
		if(is_object($val)) {
			if($val instanceof \Asgard\Form\HttpFile)
				$val = new \Asgard\Entity\Image($val->src(), $val->getName());
			$val->setWebDir($this->definition->getApp()['kernel']['webdir']);
			$val->setUrl($this->definition->getApp()['request']->url);
			$val->setFormat($this->get('format'));
			$val->setQuality($this->get('quality'));
			$val->setDir($this->get('dir'));
		}
		return $val;
	}
}