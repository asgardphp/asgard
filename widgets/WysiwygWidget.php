<?php
namespace Coxis\Form\Widgets;

class WysiwygWidget extends \Coxis\Form\Widgets\HTMLWidget {
	public function render($options=array()) {
		$options = $this->options+$options;
		
		$attrs = array(
			'rows'	=>	10,
			'cols'	=>	80,
		);
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		$id = isset($options['id']) ? $options['id']:null;
		if(!isset($options['config']))
			$options['config'] = \Coxis\Core\App::get('url')->to('ckeditor/ckeditor/config.js');
		
		\Coxis\Core\App::get('html')->include_js('ckeditor/ckeditor/ckeditor.js');
		\Coxis\Core\App::get('html')->include_js('ckeditor/ckeditor/_samples/sample.js');
		\Coxis\Core\App::get('html')->include_css('ckeditor/ckeditor/_samples/sample.css');
		return \Coxis\Form\HTMLHelper::tag('textarea', array(
			'name'	=>	$this->name,
			'id'	=>	$id,
		)+$attrs,
		$this->value ? \Coxis\Core\App::get('html')->sanitize($this->value):'').
		"<script>
		//<![CDATA[
		$(function(){
			var CKEDITOR_BASEPATH = '".\Coxis\Core\App::get('url')->to('ckeditor/ckeditor/')."';
			CKEDITOR.basePath = '".\Coxis\Core\App::get('url')->to('ckeditor/ckeditor/')."';
			var editor = CKEDITOR.instances['".$id."'];
			if (editor)
				editor.destroy(true);
			CKEDITOR.replace('".$id."'
										, {
							customConfig : '".$options['config']."'
						}
								);
		});
		//]]>
		</script>";
	}
}