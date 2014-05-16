<?php
namespace Asgard\Form\Fields;

class FileField extends \Asgard\Form\Field {
	protected $default_render = 'file';

	public function getValue() {
		$v = $this->value;
		if(is_object($v))
			return $v;
		elseif(isset($v['name']) && $v['name'])
			return array('name'=>$v['name'], 'path'=>$v['tmp_name']);
	}

	#file in memory
	// public $file_in_session = false;

	// public function setDad($dad) {
	// 	parent::setDad($dad);
	// 	$field = $this;

	// 	if($file = SESSION::get('uploadedfiles_'.$this->getName()))
	// 		$this->value = array('tmp_name'=>$file, 'name'=>basename($file), 'type'=>'', 'error'=>0, 'size'=>filesize($file));
		
	// 	$dad->hook('afterErrors', function($chain, $form) use($field) {
	// 		if(!$field->getError()) {
	// 			$file = $field->getValue();
	// 			$path = $file['tmp_name'];
	// 			$dst = 'web/tmp/'.$file['name'];
	// 			FileManager::move($path, _DIR_.$dst, true);
	// 			SESSION::set('uploadedfiles_'.$field->getName(), $dst);
	// 			$field->file_in_session = $dst;
	// 		}
	// 	});
	// }

	// public function setValue($val) {
	// 	if($val['error'] == 4)
	// 		return;
	// 	parent::setValue($val);
	// }
}