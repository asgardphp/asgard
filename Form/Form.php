<?php
namespace Asgard\Form;

/**
 * Form.
 */
class Form extends Group {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Options.
	 * @var array
	 */
	protected $options = [
		'method'	=>	'post',
		'action'	=>	'',
	];
	/**
	 * HTTP method.
	 * @var string
	 */
	protected $method = 'post';
	/**
	 * Request.
	 * @var \Asgard\Http\Request
	 */
	protected $request;
	/**
	 * Translator.
	 * @var \Symfony\Component\Translation\TranslatorInterface
	 */
	protected $translator;
	/**
	 * Save callback.
	 * @var callable
	 */
	protected $saveCallback;
	/**
	 * Pre-save callback.
	 * @var callable
	 */
	protected $preSaveCallback;
	/**
	 * Validator factory.
	 * @var \Asgard\Container\Factory
	 */
	protected $validatorFactory;

	/**
	 * Constructor.
	 * @param string               $name
	 * @param array                $options
	 * @param \Asgard\Http\Request $request
	 * @param array                $fields
	 */
	public function __construct(
		$name=null,
		array $options=[],
		\Asgard\Http\Request $request=null,
		array $fields=[]
		) {
		$this->name = $name;
		$this->options = $options;
		$this->request = $request;
		$this->setFields($fields);
		if($request === null)
			$this->getRequest();
		$this->fetch();
	}

	/**
	 * Set validator factory dependency.
	 * @param \Asgard\Container\Factory $validatorFactory
	 */
	public function setValidatorFactory($validatorFactory) {
		$this->validatorFactory = $validatorFactory;
		return $this;
	}

	/**
	 * Create a validator.
	 * @return \Asgard\Validation\Validator
	 */
	public function createValidator() {
		if($this->validatorFactory)
			return $this->validatorFactory->create();
		elseif($this->parent)
			return $this->parent->createValidator();
		else
			return new \Asgard\Validation\Validator;
	}

	/**
	 * Set the translator.
	 * @param \Symfony\Component\Translation\TranslatorInterface $translator
	 */
	public function setTranslator($translator) {
		$this->translator = $translator;
		return $this;
	}

	/**
	 * Get a translator, from this form or a parent.
	 * @return \Symfony\Component\Translation\TranslatorInterface
	 */
	public function getTranslator() {
		if($this->translator)
			return $this->translator;
		elseif($this->parent)
			return $this->parent->getTranslator();
		else
			return new \Symfony\Component\Translation\Translator('en');
	}

	/**
	 * Get container from this form or parent.
	 * @return \Asgard\Container\Container
	 */
	public function getContainer() {
		if($this->container)
			return $this->container;
		if($this->parent)
			return $this->parent->getContainer();
	}

	/**
	 * Set the request.
	 * @param \Asgard\Http\Request $request
	 */
	public function setRequest(\Asgard\Http\Request $request) {
		$this->request = $request;
		$this->fetch();
		return $this;
	}

	/**
	 * Get the request from this form or parent.
	 * @return \Asgard\Http\Request
	 */
	public function getRequest() {
		$r = parent::getRequest();
		if($r === null)
			$this->request = $r = \Asgard\Http\Request::createFromGlobals();
		return $r;
	}

	/**
	 * Set the HTTP method.
	 * @param string $method
	 */
	public function setMethod($method) {
		$this->method = $method;
		return $this;
	}

	/**
	 * Get the HTTP method.
	 * @return string
	 */
	public function getMethod() {
		return strtoupper($this->method);
	}

	/**
	 * Set an option.
	 * @param string $option
	 * @param mixed $value
	 */
	public function setOption($option, $value) {
		$this->options[$option] = $value;
		return $this;
	}

	/**
	 * Get an option.
	 * @param  string $option
	 * @return mixed
	 */
	public function getOption($option) {
		if(!isset($this->options[$option]))
			return;
		return $this->options[$option];
	}

	/**
	 * Activate CSRF protection.
	 * @param  boolean $active
	 * @return Form    $this
	 */
	public function csrf($active=true) {
		if($active)
			$this->add(new Fields\CSRFField, '_csrf_token');
		else
			$this->remove('_csrf_token');
		return $this;
	}

	/**
	 * Set the save callback.
	 * @param callable $saveCallback
	 */
	public function setSaveCallback($saveCallback) {
		$this->saveCallback = $saveCallback;
		return $this;
	}

	/**
	 * Set the pre-save callback.
	 * @param callable $preSaveCallback
	 */
	public function setPreSaveCallback($preSaveCallback) {
		$this->preSaveCallback = $preSaveCallback;
		return $this;
	}

	/**
	 * Save the form and its children.
	 * @return boolean  true for success
	 */
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

	/**
	 * Actually perform the save. Does nothing by default but calls the save callback and can be overriden.
	 */
	public function doSave() {
		if($cb = $this->saveCallback)
			$cb($this);
	}

	/**
	 * Check if form was sent.
	 * @return boolean
	 */
	public function sent() {
		if($this->parent)
			return $this->parent->sent();

		$method = $this->getMethod();
		if($method !== $this->getRequest()->method())
			return false;

		#if form has a name
		if($this->name) {
			if($method == 'POST' || $method == 'PUT')
				return $this->getRequest()->post->has($this->name);
			elseif($method == 'GET')
				return $this->getRequest()->get->has($this->name);
		}
		#otherwise we try to guess by comparing fields
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

	/**
	 * Get errors not belonging to a specific field or hidden ones.
	 * @return array
	 */
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

	/**
	 * Check if form is valid.
	 * @return boolean true for success
	 */
	public function isValid() {
		return $this->sent() && !$this->errors();
	}

	/**
	 * Check if content was uploaded successfully.
	 * @return boolean true for success
	 */
	public function uploadSuccess() {
		return $this->getRequest()->server['CONTENT_LENGTH'] <= (int)ini_get('post_max_size')*1024*1024;
	}
	
	/**
	 * Return the opening form tag.
	 * @param  array $options
	 * @return string
	 */
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
	
	/**
	 * Return the closing form tag.
	 * @return string
	 */
	public function close() {
		$str = '';
		if($this->has('_csrf_token'))
			$str .= $this['_csrf_token']->def();
		$str .= '</form>';
		
		return $str;
	}
	
	/**
	 * Return the submit button.
	 * @param  mixed $value
	 * @param  array $options
	 * @return string
	 */
	public function submit($value, array $options=[]) {
		return HTMLHelper::tag('input', array_merge([
			'type'		=>	'submit',
			'value'	=>	$value,
		], $options));
	}

	/**
	 * Set the parent.
	 * @param Group $parent
	 */
	public function setParent(Group $parent) {
		#disable CSRF when form belongs to another
		$this->csrf(false);
		return parent::setParent($parent);
	}

	/**
	 * Fetch data.
	 * @return Form $this
	 */
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
