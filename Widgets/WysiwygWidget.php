<?php
namespace Asgard\Form\Widgets;

class WysiwygWidget extends \Asgard\Form\Widgets\HTMLWidget {
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
			$options['config'] = \Asgard\Core\App::get('url')->to('ckeditor/ckeditor/config.js');
		
		\Asgard\Core\App::get('html')->includeJS('ckeditor/ckeditor/ckeditor.js');
		\Asgard\Core\App::get('html')->includeJS('ckeditor/ckeditor/_samples/sample.js');
		\Asgard\Core\App::get('html')->includeCSS('ckeditor/ckeditor/_samples/sample.css');
		return \Asgard\Form\HTMLHelper::tag('textarea', array(
			'name'	=>	$this->name,
			'id'	=>	$id,
		)+$attrs,
		$this->value ? \Asgard\Core\App::get('html')->sanitize($this->value):'').
		"<script>
		//<![CDATA[
		$(function(){
			var CKEDITOR_BASEPATH = '".\Asgard\Core\App::get('url')->to('ckeditor/ckeditor/')."';
			CKEDITOR.basePath = '".\Asgard\Core\App::get('url')->to('ckeditor/ckeditor/')."';
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