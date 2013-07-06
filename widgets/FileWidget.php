<?php
namespace Coxis\Form\Widgets;

class FileWidget extends \Coxis\Form\Widgets\HTMLWidget {
	public function render($options=array()) {
		$options = $this->options+$options;#todo reprendre dans les autres widgets
		
		$attrs = array();
		if(isset($options['attrs']))
			$attrs = $options['attrs'];

		$str = HTMLHelper::tag('input', array(
			'type'	=>	'file',
			'name'	=>	$this->name,
			'id'	=>	isset($options['id']) ? $options['id']:null,
		)+$attrs);

		#file in memory
		// if($options['field']->file_in_session)
		// 	$str .= ' (<a href="'.$options['field']->file_in_session.'">your file</a>)';

		return $str;
	}
}