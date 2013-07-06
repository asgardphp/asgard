<?php
namespace Coxis\Form\Widgets;

class WysiwygWidget extends \Coxis\Form\Widgets\HTMLWidget {
	public function render($options=null) {
		if($options === null)
			$options = $this->options;
		
		$attrs = array(
			'rows'	=>	10,
			'cols'	=>	80,
		);
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		$id = isset($options['id']) ? $options['id']:null;
		if(!isset($options['config']))
			$options['config'] = \URL::to('ckeditor/ckeditor/config.js');
		
		HTML::include_js('ckeditor/ckeditor/ckeditor.js');
		HTML::include_js('ckeditor/ckeditor/_samples/sample.js');
		HTML::include_css('ckeditor/ckeditor/_samples/sample.css');
		return HTMLHelper::tag('textarea', array(
			'name'	=>	$this->name,
			'id'	=>	$id,
		)+$attrs,
		$this->value ? HTML::sanitize($this->value):'').
		"<script>
		//<![CDATA[
		$(function(){
			var CKEDITOR_BASEPATH = '".\URL::to('ckeditor/ckeditor/')."';
			CKEDITOR.basePath = '".\URL::to('ckeditor/ckeditor/')."';
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