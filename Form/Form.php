<?php
namespace Asgard\Form;

class Form extends Group {
	protected $params = [
		'method'	=>	'post',
		'action'	=>	'',
	];
	protected $render_callbacks = [];
	protected $method = 'post';
	protected $request;
	protected $translator;
	protected $hooks;
	protected $app;

	public function __construct(
		$name=null,
		$params=[],
		$fields=[],
		\Asgard\Http\Request $request=null
		) {
		$this->groupName = $name;
		$this->params = $params;
		$this->request = $request;
		if($request)
			$this->fetch();
		$this->addFields($fields);
	}

	public function setHooks($hooks) {
		$this->hooks = $hooks;
		return $this;
	}

	public function setTranslator($translator) {
		$this->translator = $translator;
		return $this;
	}

	public function setParam($param, $value) {
		$this->params[$param] = $value;
		return $this;
	}

	public function getParam() {
		if(!isset($this->params[$param]))
			return;
		return $this->params[$param];
	}

	public function getApp() {
		if($this->app)
			return $this->app;
		if($this->dad)
			return $this->dad->getApp();
	}

	public function setApp($app) {
		$this->app = $app;
	}

	public function getHooks() {
		if($this->hooks)
			return $this->hooks;
		if($this->dad)
			return $this->dad->getHooks();
		else
			return \Asgard\Hook\HooksManager::instance();
	}

	public function getTranslator() {
		if($this->translator)
			return $this->translator;
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
		$raw = [];
		$files = [];
			
		if($this->groupName) {
			if($this->getRequest()->file->get($this->groupName) !== null)
				$raw = $this->getRequest()->file->get($this->groupName);
			else
				$raw = [];
		}
		else
			$raw = $this->getRequest()->file->all();


		$files = $this->parseFiles($raw);

		$this->data = [];
		if($this->groupName) {
			$this->setData(
				$this->getRequest()->post->get($this->groupName, []) + $files
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
	
	public function open(array $params=[]) {
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
		echo HTMLHelper::tag('input', [
			'type'		=>	'submit',
			'value'	=>	$value,
		]);
		
		return $this;
	}

	public function getGeneralErrors() {
		if(!$this->errors)
			return;
		$gen_errors = [];
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
		$res = [];
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
			}
			else
				$files = $raw;
		}
		else {
			foreach($raw as $k=>$v) {
				if($v['error'] == 4)
					continue;
				else
					$raw[$k] = $this->parseFiles($v);
			}
			$files = $raw;
		}

		foreach($files as $k=>$v)
			$files[$k] = \Asgard\Form\HttpFile::createFromArray($v);

		return $files;
	}
}
