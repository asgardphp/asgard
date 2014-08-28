<?php
namespace Asgard\Form;

class Form extends Group {
	use \Asgard\Hook\HookableTrait;
	use \Asgard\Container\ContainerAwareTrait;

	protected $options = [
		'method'	=>	'post',
		'action'	=>	'',
	];
	protected $method = 'post';
	protected $request;
	protected $translator;
	protected $saveCallback;
	protected $preSaveCallback;

	/* Constructor */
	public function __construct(
		$name=null,
		$options=[],
		\Asgard\Http\Request $request=null,
		$fields=[]
		) {
		$this->name = $name;
		$this->options = $options;
		$this->request = $request;
		$this->setFields($fields);
		if($request === null)
			$this->getRequest();
		$this->fetch();
	}

	/* Dependencies */
	public function setTranslator($translator) {
		$this->translator = $translator;
		return $this;
	}

	public function getTranslator() {
		if($this->translator)
			return $this->translator;
		elseif($this->parent)
			return $this->parent->getTranslator();
		else
			return new \Symfony\Component\Translation\Translator('en');
	}

	public function getContainer() {
		if($this->container)
			return $this->container;
		if($this->parent)
			return $this->parent->getContainer();
	}

	public function setRequest(\Asgard\Http\Request $request) {
		$this->request = $request;
		$this->fetch();
		return $this;
	}

	public function getRequest() {
		$r = parent::getRequest();
		if($r === null)
			$this->request = $r = \Asgard\Http\Request::createFromGlobals();
		return $r;
	}

	/* optioneters */
	public function setMethod($method) {
		$this->method = $method;
		return $this;
	}

	public function getMethod() {
		return strtoupper($this->method);
	}

	public function setOption($option, $value) {
		$this->options[$option] = $value;
		return $this;
	}

	public function getOption($option) {
		if(!isset($this->options[$option]))
			return;
		return $this->options[$option];
	}

	/* CSRF */
	public function csrf($active=true) {
		if($active)
			$this->add(new Fields\CSRFField, '_csrf_token');
		else
			$this->remove('_csrf_token');
		return $this;
	}

	/* Callbacks */
	public function setSaveCallback($saveCallback) {
		$this->saveCallback = $saveCallback;
		return $this;
	}

	public function setPreSaveCallback($preSaveCallback) {
		$this->preSaveCallback = $preSaveCallback;
		return $this;
	}

	/* Validation & Save */
	public function doSave() {
		if($cb = $this->saveCallback)
			$cb($this);
	}

	public function save() {
		if($errors = $this->errors()) {
			$e = new FormException;
			$e->errors = $errors;
			throw $e;
		}
		if(!$this->sent())
			return;
	
		if($cb = $this->preSaveCallback)
			$cb($this);
	
		return $this->_save();
	}

	public function sent() {
		if($this->parent)
			return $this->parent->sent();

		$method = $this->getMethod();
		if($method !== $this->getRequest()->method())
			return false;

		if($this->name) {
			if($method == 'POST' || $method == 'PUT')
				return $this->getRequest()->post->has($this->name);
			elseif($method == 'GET')
				return $this->getRequest()->get->has($this->name);
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
		}

		return false;
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
		return $this->sent() && !$this->errors();
	}

	public function uploadSuccess() {
		return $this->getRequest()->server['CONTENT_LENGTH'] <= (int)ini_get('post_max_size')*1024*1024;
	}
	
	/* Rendeinrg */
	public function open(array $options=[]) {
		$options = array_merge($this->options, $options);
		$action = isset($options['action']) && $options['action'] ? $options['action']:$this->request->url->full();
		$method = $this->method;
		$enctype = isset($options['enctype']) ? ' enctype="'.$options['enctype'].'"':($this->hasFile() ? ' enctype="multipart/form-data"':'');
		$attrs = '';
		if(isset($options['attrs'])) {
			foreach($options['attrs'] as $k=>$v)
				$attrs .= ' '.$k.'="'.$v.'"';
		}
		return '<form action="'.$action.'" method="'.$method.'"'.$enctype.$attrs.'>'."\n";
	}
	
	public function close() {
		$str = '';
		if($this->has('_csrf_token'))
			$str .= $this['_csrf_token']->def();
		$str .= '</form>';
		
		return $str;
	}
	
	public function submit($value, $options=[]) {
		return HTMLHelper::tag('input', array_merge([
			'type'		=>	'submit',
			'value'	=>	$value,
		], $options));
	}

	/* Internal */
	public function setParent(Group $parent) {
		$this->parent = $parent;
		$this->csrf(false);
		return $this;
	}

	public function fetch() {
		if($this->name) {
			if($this->getRequest()->file->get($this->name) !== null)
				$files = $this->getRequest()->file->get($this->name);
			else
				$files = [];
		}
		else
			$files = $this->getRequest()->file->all();

		foreach($files as $k=>$file) {
			if($file->error() === 4)
				unset($files[$k]);
		}

		$this->data = [];
		if($this->name) {
			$this->setData(
				$this->getRequest()->post->get($this->name, []) + $files
			);
		}
		else
			$this->setData($this->getRequest()->post->all() + $files);

		return $this;
	}
}
