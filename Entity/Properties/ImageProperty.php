<?php
namespace Asgard\Entity\Properties;

class ImageProperty extends FileProperty {
	protected static $defaultExtensions = array('png', 'jpg', 'jpeg', 'gif');

	protected function doUnserialize($str, $entity=null) {
		if(!$str)
			return null;
		$image = new \Asgard\Files\Image($str);
		$image->setWebDir($this->definition->getApp()['kernel']['webdir']);
		$image->setUrl($this->definition->getApp()['request']->url);
		return $image;
	}

	protected function doSet($val, $entity=null) {
		if(is_string($val) && $val !== null)
			$val = new \Asgard\Files\Image($val);
		if(is_object($val)) {
			if($val instanceof \Asgard\Form\HttpFile)
				$val = new \Asgard\Files\Image($val->src(), $val->getName());
			$val->setWebDir($this->definition->getApp()['kernel']['webdir']);
			$val->setUrl($this->definition->getApp()['request']->url);
		}
		return $val;
	}
}