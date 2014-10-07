<?php
namespace Asgard\Form;

/**
 * Form.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface FormInterface extends GroupInterface {
	/**
	 * Set validator factory dependency.
	 * @param \Asgard\Container\Factory $validatorFactory
	 */
	public function setValidatorFactory($validatorFactory);

	/**
	 * Create a validator.
	 * @return \Asgard\Validation\ValidatorInterface
	 */
	public function createValidator();

	/**
	 * Set the translator.
	 * @param \Symfony\Component\Translation\TranslatorInterface $translator
	 */
	public function setTranslator($translator);

	/**
	 * Get a translator, from this form or a parent.
	 * @return \Symfony\Component\Translation\TranslatorInterface
	 */
	public function getTranslator();

	/**
	 * Get container from this form or parent.
	 * @return \Asgard\Container\ContainerInterface
	 */
	public function getContainer();

	/**
	 * Set the request.
	 * @param \Asgard\Http\Request $request
	 */
	public function setRequest(\Asgard\Http\Request $request);

	/**
	 * Get the request from this form or parent.
	 * @return \Asgard\Http\Request
	 */
	public function getRequest();

	/**
	 * Set the HTTP method.
	 * @param string $method
	 */
	public function setMethod($method);

	/**
	 * Get the HTTP method.
	 * @return string
	 */
	public function getMethod();

	/**
	 * Set an option.
	 * @param string $option
	 * @param mixed $value
	 */
	public function setOption($option, $value);

	/**
	 * Get an option.
	 * @param  string $option
	 * @return mixed
	 */
	public function getOption($option);

	/**
	 * Activate CSRF protection.
	 * @param  boolean       $active
	 * @return FormInterface $this
	 */
	public function csrf($active=true);

	/**
	 * Set the save callback.
	 * @param callable $saveCallback
	 */
	public function setSaveCallback($saveCallback);

	/**
	 * Set the pre-save callback.
	 * @param callable $preSaveCallback
	 */
	public function setPreSaveCallback($preSaveCallback);

	/**
	 * Save the form and its children.
	 * @return boolean  true for success
	 */
	public function save();

	/**
	 * Actually perform the save. Does nothing by default but calls the save callback and can be overriden.
	 */
	public function doSave();

	/**
	 * Check if form was sent.
	 * @return boolean
	 */
	public function sent();

	/**
	 * Get errors not belonging to a specific field or hidden ones.
	 * @return array
	 */
	public function getGeneralErrors();

	/**
	 * Check if form is valid.
	 * @return boolean true for success
	 */
	public function isValid();

	/**
	 * Check if content was uploaded successfully.
	 * @return boolean true for success
	 */
	public function uploadSuccess();

	/**
	 * Return the opening form tag.
	 * @param  array $options
	 * @return string
	 */
	public function open(array $options=[]);

	/**
	 * Return the closing form tag.
	 * @return string
	 */
	public function close();

	/**
	 * Return the submit button.
	 * @param  mixed $value
	 * @param  array $options
	 * @return string
	 */
	public function submit($value, array $options=[]);

	/**
	 * Set the parent.
	 * @param GroupInterface $parent
	 */
	public function setParent(GroupInterface $parent);

	/**
	 * Fetch data.
	 * @return FormInterface $this
	 */
	public function fetch();
}
