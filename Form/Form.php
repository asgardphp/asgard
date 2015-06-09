<?php
namespace Asgard\Form;

/**
 * Form.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Form extends Group implements FormInterface {
	/**
	 * Options.
	 * @var array
	 */
	protected $options = [
		'action' => '',
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
	 * @var \Asgard\Validation\ValidatorFactoryInterface
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
		$name                         = null,
		array $options                = [],
		\Asgard\Http\Request $request = null,
		array $fields                 = []
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
	 * {@inheritDoc}
	 */
	public function setValidatorFactory(\Asgard\Validation\ValidatorFactoryInterface $validatorFactory) {
		$this->validatorFactory = $validatorFactory;
		return $this;
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function setTranslator($translator) {
		$this->translator = $translator;
		return $this;
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function setRequest(\Asgard\Http\Request $request) {
		$this->request = $request;
		$this->fetch();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequest() {
		$r = parent::getRequest();
		if($r === null)
			$this->request = $r = \Asgard\Http\Request::createFromGlobals();
		return $r;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setMethod($method) {
		$this->method = $method;
		$this->fetch();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMethod() {
		return strtoupper($this->method);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setOption($option, $value) {
		$this->options[$option] = $value;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOption($option) {
		if(!isset($this->options[$option]))
			return;
		return $this->options[$option];
	}

	/**
	 * {@inheritDoc}
	 */
	public function csrf($active=true) {
		if($active)
			$this->add(new Fields\CSRFField, '_csrf_token');
		else
			$this->remove('_csrf_token');
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSaveCallback($saveCallback) {
		$this->saveCallback = $saveCallback;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPreSaveCallback($preSaveCallback) {
		$this->preSaveCallback = $preSaveCallback;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function save($validationGroups=[]) {
		$errors = $this->errors($validationGroups);
		if(!$errors->valid()) {
			$e = new FormException;
			$e->setReport($errors);
			throw $e;
		}
		if(!$this->sent())
			return;

		if($cb = $this->preSaveCallback)
			$cb($this);

		return $this->_save();
	}

	/**
	 * {@inheritDoc}
	 */
	public function doSave() {
		if($cb = $this->saveCallback)
			$cb($this);
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function getGeneralErrors() {
		if(!$this->errors)
			return;
		$gen_errors = $this->errors->getRulesErrors();
		foreach($this->errors->attributes() as $field_name=>$errors) {
			if(!$this->has($field_name) || $this->get($field_name) instanceof Fields\HiddenField)
				$gen_errors[$field_name] = $errors->errors();
		}
		return $gen_errors;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isValid($validationGroups=[]) {
		return $this->sent() && $this->errors($validationGroups)->valid();
	}

	/**
	 * {@inheritDoc}
	 */
	public function uploadSuccess() {
		return $this->getRequest()->server['CONTENT_LENGTH'] <= (int)ini_get('post_max_size')*1024*1024;
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function close() {
		$str = '';
		if($this->has('_csrf_token'))
			$str .= $this['_csrf_token']->def();
		$str .= '</form>';

		return $str;
	}

	/**
	 * {@inheritDoc}
	 */
	public function submit($value, array $options=[]) {
		return HTMLHelper::tag('input', array_merge([
			'type'		=>	'submit',
			'value'	=>	$value,
		], $options));
	}

	/**
	 * {@inheritDoc}
	 */
	public function setParent(GroupInterface $parent) {
		#disable CSRF when form belongs to another
		$this->csrf(false);
		return parent::setParent($parent);
	}

	/**
	 * {@inheritDoc}
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
		
		if($this->getMethod() == 'GET')
			$input = $this->getRequest()->get;
		else
			$input = $this->getRequest()->post;

		$this->data = [];
		if($this->name) {
			$this->setData(
				$input->get($this->name, []) + $files
			);
		}
		else
			$this->setData($input->all() + $files);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function doRender($render_callback, $field, array &$options) {
		$name = $field->name();
		if($field instanceof Field) {
			$options['field'] = $field;
			$options = $field->options+$options;
			$options['id'] = $field->getID();
			$value = $field->value();
		}
		elseif($field instanceof GroupInterface) {
			$options['group'] = $field;
			$value = null;
		}
		else
			throw new \Exception('Invalid field type.');

		$widget = $this->getWidget($render_callback, $name, $value, $options);
		if($widget === null)
			throw new \Exception('Invalid widget name: '.$render_callback);

		return $widget;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getWidget($widget, $name, $value, array $options=[]) {
		return $this->getWidgetManager()->getWidget($widget, $name, $value, $options, $this);
	}
}
