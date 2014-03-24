<?php
namespace Asgard\Form;

class Form extends Group {
	protected $_params = array(
		'method'	=>	'post',
		'action'	=>	'',
	);
	protected $_render_callbacks = array();
	protected $_method = 'post';

	function __construct($name=null, $params=array(), $fields=array()) {
		$this->_groupName = $name;
		$this->_params = $params;
		$this->fetch();
		$this->setFields($fields);
	}

	public function csrf() {
		$this->add('_csrf_token', '\Asgard\Form\Fields\CSRF');
		return $this;
	}

	public function noCSRF() {
		if($this->has('_csrf_token'))
			$this->remove('_csrf_token');
		return $this;
	}
	
	public function setDad($dad) {
		$this->_dad = $dad;
		$this->noCSRF();
		return $this;
	}

	public function setMethod($method) {
		$this->_method = $method;
		return $this;
	}

	public function getMethod() {
		return strtoupper($this->_method);
	}

	public function render($render_callback, $field, $options=array()) {
		if($this->_dad)
			return $this->_dad->render($render_callback, $field, $options);

		#render function passed by argument
		if(\Asgard\Utils\Tools::is_function($render_callback))
			$cb = $render_callback;
		#render function defined by setRenderCallback()
		elseif(isset($this->_render_callbacks[$render_callback]))
			$cb = $this->_render_callbacks[$render_callback];
		else {
			$cb = function($field, $options=array()) use($render_callback) {
				#widget given by a form hook
				$widget = $this->trigger('Asgard\Form\Widgets\\'.$render_callback);
				if($widget === null) {
					#widget given by an application hook
					$widget = \Asgard\Core\App::get('hook')->trigger('Asgard\Form\Widgets\\'.$render_callback, array(), function() use($render_callback) {
						#if $render_callback is a widget class
						if(class_exists($render_callback) && $render_callback instanceof \Asgard\Form\Widgets\HTMLWidget)
							return $render_callback;
						#last chance
						elseif(class_exists('Asgard\Form\Widgets\\'.$render_callback.'Widget') && is_subclass_of('Asgard\Form\Widgets\\'.$render_callback.'Widget', '\Asgard\Form\Widgets\HTMLWidget'))
							return 'Asgard\Form\Widgets\\'.$render_callback.'Widget';
						else
							throw new \Exception('No widget for callback: '.$render_callback);
					});
				}
				return \Asgard\Form\Widgets\HTMLWidget::getWidget($widget, array($field->getName(), $field->getValue(), $options));
			};
		}

		if(!$cb)
			throw new \Exception('Render callback "'.$render_callback.'" does not exist.');

		$options = $field->options+$options;
		$options['field'] = $field;
		$options['id'] = $field->getID();

		if($this->hasHook('render'))
			$res = $this->trigger('render', array($field, $cb($field, $options), $options));
		else
			return $cb($field, $options);

		return $res;
	}

	public function uploadSuccess() {
		return $this->getRequest()->server->get('CONTENT_LENGTH') <= (int)ini_get('post_max_size')*1024*1024;
	}

	public function setRenderCallback($name, $callback) {
		$this->_render_callbacks[$name] = $callback;
	}

	public function setRequest($request) {
		$this->_request = $request;
		$this->fetch();
		return $this;
	}
	
	public function fetch() {
		$raw = array();
		$files = array();
			
		if($this->_groupName) {
			if($this->getRequest()->file->get($this->_groupName) !== null)
				$raw = $this->getRequest()->file->get($this->_groupName);
			else
				$raw = array();
		}
		else
			$raw = $this->getRequest()->file->all();

		$files = $this->parseFiles($raw);

		$this->_data = array();
		if($this->_groupName) {
			$this->setData(
				$this->getRequest()->post->get($this->_groupName, array()),
				$files
			);
		}
		else
			$this->setData($this->getRequest()->post->all(), $files);

		return $this;
	}

	public function isSent() {
		if($this->_dad)
			return $this->_dad->isSent();

		$method = $this->getMethod();
		if($method !== $this->getRequest()->method())
			return false;

		if($this->_groupName) {
			if($method == 'POST' || $method == 'PUT')
				return $this->getRequest()->post->has($this->_groupName);
			elseif($method == 'GET')
				return $this->getRequest()->get->has($this->_groupName);
			return false;
		}
		else {
			if($method == 'POST' || $method == 'PUT')
				$input = $this->getRequest()->post;
			elseif($method == 'GET')
				$input = $this->getRequest()->get;
			else
				return false;
			foreach($input->all() as $k=>$v) {
				if($this->has($k))
					return true;
			}
			return false;
		}

		return false;
	}
	
	public function open($params=array()) {
		$params = array_merge($this->_params, $params);
		$action = isset($params['action']) && $params['action'] ? $params['action']:\Asgard\Core\App::get('url')->full();
		$method = $this->_method;
		$enctype = isset($params['enctype']) ? $params['enctype']:($this->hasFile() ? ' enctype="multipart/form-data"':'');
		$attrs = '';
		if(isset($params['attrs'])) {
			foreach($params['attrs'] as $k=>$v)
				$attrs .= ' '.$k.'="'.$v.'"';
		}
		echo '<form action="'.$action.'" method="'.$method.'"'.$enctype.$attrs.'>'."\n";
		
		return $this;
	}
	
	public function close() {
		if($this->has('_csrf_token'))
			echo $this->_csrf_token->def();
		echo '</form>';
		
		return $this;
	}
	
	public function submit($value) {
		echo HTMLHelper::tag('input', array(
			'type'		=>	'submit',
			'value'	=>	$value,
		));
		
		return $this;
	}

	public function getGeneralErrors() {
		if(!$this->_errors)
			return;
		$gen_errors = array();
		foreach($this->_errors as $field_name=>$errors) {
			if(!$this->has($field_name) || $this->get($field_name) instanceof Fields\HiddenField)
				$gen_errors[$field_name] = $errors;
		}
		return $gen_errors;
	}

	public function isValid() {
		return !$this->errors() && $this->isSent();
	}
	
	protected function convertTo($type, $files) {
		$res = array();
		foreach($files as $name=>$file) {
			if(is_array($file))
				$res[$name] = $this->convertTo($type, $file);
			else
				$res[$name][$type] = $file;
		}
				
		return $res;
	}
	
	protected function merge_all($name, $type, $tmp_name, $error, $size) {
		foreach($name as $k=>$v) {
			if(isset($v['name']) && !is_array($v['name']))
				$name[$k] = array_merge($v, $type[$k], $tmp_name[$k], $error[$k], $size[$k]);
			else 
				$name[$k] = $this->merge_all($name[$k], $type[$k], $tmp_name[$k], $error[$k], $size[$k]);
		}
		
		return $name;
	}

	protected function parseFiles($raw) {
		if(isset($raw['name']) && isset($raw['type']) && isset($raw['tmp_name']) && isset($raw['error']) && isset($raw['size'])) {
			if(is_array($raw['name'])) {
				$name = $this->convertTo('name', $raw['name']);
				$type = $this->convertTo('type', $raw['type']);
				$tmp_name = $this->convertTo('tmp_name', $raw['tmp_name']);
				$error = $this->convertTo('error', $raw['error']);
				$size = $this->convertTo('size', $raw['size']);
				
				$files = $this->merge_all($name, $type, $tmp_name, $error, $size);
				return $files;
			}
			else
				return $raw;
		}
		else {
			foreach($raw as $k=>$v) {
				if($v['error'] == 4)
					continue;
				else
					$raw[$k] = $this->parseFiles($v);
			}
			return $raw;
		}
	}
}
