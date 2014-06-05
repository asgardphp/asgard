<?php
namespace Asgard\Form;

class Form extends Group {
	protected $params = array(
		'method'	=>	'post',
		'action'	=>	'',
	);
	protected $render_callbacks = array();
	protected $method = 'post';
	protected $request;
	protected $app;

	public function __construct(
		$name=null,
		$params=array(),
		$fields=array(),
		\Asgard\Http\Request $request=null,
		$app=null // for hooks, translator et widgets constructor
		) {
		$this->groupName = $name;
		$this->params = $params;
		$this->request = $request;
		$this->app = $app;
		if($request)
			$this->fetch();
		$this->addFields($fields);
	}

	public function setParam($param, $value) {
		$this->params[$param] = $value;
	}

	public function getParam() {
		if(!isset($this->params[$param]))
			return;
		return $this->params[$param];
	}

	public function getApp() {
		if(!$this->app)
			return $this->dad->getApp();
		return $this->app;
	}

	public function setApp($app) {
		$this->app = $app;
	}

	public function getHooksManager() {
		if($this->app)
			return $this->app['hooks'];
		if($this->dad)
			return $this->dad->getHooksManager();
	}

	public function getTranslator() {
		if($this->app)
			return $this->app['translator'];
		if($this->dad)
			return $this->dad->getTranslator();
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
	
	public function setDad(Group $dad) {
		$this->dad = $dad;
		$this->noCSRF();
		return $this;
	}

	public function setMethod($method) {
		$this->method = $method;
		return $this;
	}

	public function getMethod() {
		return strtoupper($this->method);
	}

	public function getWidget($class, $name, $value, $options) {
		#Asgard\Form\Widgets\TextWidget
		if(class_exists($class)) {
			$reflector = new \ReflectionClass($class);
			return $reflector->newInstanceArgs(array($name, $value, $options, $this));
		}
		#text
		else {
			$form = $this;
			return $this->app->make('Asgard\Form\Widgets\\'.$class, array($name, $value, $options, $form), function() use($class, $name, $value, $options, $form) {
				$class = 'Asgard\Form\Widgets\\'.$class.'Widget';
				$reflector = new \ReflectionClass($class);
				$widget = $reflector->newInstanceArgs(array($name, $value, $options, $form));
				return $widget;
			});
		}
	}

	public function render($render_callback, Field $field, array $options=array()) {
		if($this->dad)
			return $this->dad->render($render_callback, $field, $options);

		#render function passed by argument
		if(\Asgard\Utils\Tools::is_function($render_callback))
			$cb = $render_callback;
		#render function defined by setRenderCallback()
		elseif(isset($this->render_callbacks[$render_callback]))
			$cb = $this->render_callbacks[$render_callback];
		else {
			$cb = function($field, $options=array()) use($render_callback) {
				#widget given by a form hook
				$widget = $this->trigger('Widgets.'.$render_callback);
				if($widget === null) {
					#widget given by an application hook
					if(!$this->getHooksManager() || !($widget = $this->getHooksManager()->trigger('Asgard.Form.Widgets.'.$render_callback))) {
						#if $render_callback is a widget class
						if(class_exists($render_callback) && $render_callback instanceof \Asgard\Form\Widget)
							$widget = $render_callback;
						#last chance
						elseif(class_exists('Asgard\Form\Widgets\\'.ucfirst($render_callback).'Widget') && is_subclass_of('Asgard\Form\Widgets\\'.ucfirst($render_callback).'Widget', '\Asgard\Form\Widget'))
							$widget = 'Asgard\Form\Widgets\\'.ucfirst($render_callback).'Widget';
						else
							throw new \Exception('No widget for callback: '.$render_callback);
					}
				}

				return $this->getWidget($widget, $field->getName(), $field->getValue(), $options);
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
		return $this->getRequest()->server['CONTENT_LENGTH'] <= (int)ini_get('post_max_size')*1024*1024;
	}

	public function setRenderCallback($name, $callback) {
		$this->render_callbacks[$name] = $callback;
	}

	public function setRequest(\Asgard\Http\Request $request) {
		$this->request = $request;
		$this->fetch();
		return $this;
	}
	
	public function fetch() {
		$raw = array();
		$files = array();
			
		if($this->groupName) {
			if($this->getRequest()->file->get($this->groupName) !== null)
				$raw = $this->getRequest()->file->get($this->groupName);
			else
				$raw = array();
		}
		else
			$raw = $this->getRequest()->file->all();

		$files = $this->parseFiles($raw);

		$this->data = array();
		if($this->groupName) {
			$this->setData(
				$this->getRequest()->post->get($this->groupName, array()) + $files
			);
		}
		else
			$this->setData($this->getRequest()->post->all() + $files);

		return $this;
	}

	public function isSent() {
		if($this->dad)
			return $this->dad->isSent();

		$method = $this->getMethod();
		if($method !== $this->getRequest()->method())
			return false;

		if($this->groupName) {
			if($method == 'POST' || $method == 'PUT')
				return $this->getRequest()->post->has($this->groupName);
			elseif($method == 'GET')
				return $this->getRequest()->get->has($this->groupName);
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
	
	public function open(array $params=array()) {
		$params = array_merge($this->params, $params);
		$action = isset($params['action']) && $params['action'] ? $params['action']:$this->request->url->full();
		$method = $this->method;
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
			echo $this['_csrf_token']->def();
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
		if(!$this->errors)
			return;
		$gen_errors = array();
		foreach($this->errors as $field_name=>$errors) {
			if(!$this->has($field_name) || $this->get($field_name) instanceof Fields\HiddenField)
				$gen_errors[$field_name] = $errors;
		}
		return $gen_errors;
	}

	public function isValid() {
		return $this->isSent() && !$this->errors();
	}
	
	protected function convertTo($type, array $files) {
		$res = array();
		foreach($files as $name=>$file) {
			if(is_array($file))
				$res[$name] = $this->convertTo($type, $file);
			else
				$res[$name][$type] = $file;
		}
				
		return $res;
	}
	
	protected function merge_all(array $name, array $type, array $tmp_name, array $error, array $size) {
		foreach($name as $k=>$v) {
			if(isset($v['name']) && !is_array($v['name']))
				$name[$k] = array_merge($v, $type[$k], $tmp_name[$k], $error[$k], $size[$k]);
			else 
				$name[$k] = $this->merge_all($name[$k], $type[$k], $tmp_name[$k], $error[$k], $size[$k]);
		}
		
		return $name;
	}

	protected function parseFiles(array $raw) {
		if(isset($raw['name']) && isset($raw['type']) && isset($raw['tmp_name']) && isset($raw['error']) && isset($raw['size'])) {
			if(is_array($raw['name'])) {
				$name = $this->convertTo('name', $raw['name']);
				$type = $this->convertTo('type', $raw['type']);
				$tmp_name = $this->convertTo('tmp_name', $raw['tmp_name']);
				$error = $this->convertTo('error', $raw['error']);
				$size = $this->convertTo('size', $raw['size']);
				
				$files = $this->merge_all($name, $type, $tmp_name, $error, $size);
				// return $files;
			}
			else
				// return $raw;
				$files = $raw;
		}
		else {
			foreach($raw as $k=>$v) {
				if($v['error'] == 4)
					continue;
				else
					$raw[$k] = $this->parseFiles($v);
			}
			// return $raw;
			$files = $raw;
		}

		foreach($files as $k=>$v)
			$files[$k] = \Asgard\Form\HttpFile::createFromArray($v);

		return $files;
	}
}
