<?php
namespace Asgard\Validation;
use Symfony\Component\Translation\TranslatorInterface;

 /**
  * Validator.
  */
interface ValidatorInterface {
	/**
	 * Set the translator.
	 * @param \Symfony\Component\Translation\TranslatorInterface $translator
	 */
	public function setTranslator(TranslatorInterface $translator);

	/**
	 * Get the translator.
	 * @return \Symfony\Component\Translation\TranslatorInterface
	 */
	public function getTranslator();

	/**
	 * Set the default message for an attribute.
	 * @param string $attribute attribute name
	 * @param string $message
	 * @return ValidatorInterface       $this
	 */
	public function setDefaultMessage($attribute, $message=null);

	/**
	 * Get the default error message.
	 * @return string
	 */
	public function getDefaultMessage();

	/**
	 * Set the default message for a rule.
	 * @param  string $rule    rule name
	 * @param  string $message
	 * @return ValidatorInterface       $this
	 */
	public function ruleMessage($rule, $message=null);

	/**
	 * Set multiple rules messages.
	 * @param  array  $rules
	 * @return ValidatorInterface       $this
	 */
	public function ruleMessages(array $rules);

	/**
	 * Set multiple attributes messages.
	 * @param  array  $messages
	 * @return ValidatorInterface       $this
	 */
	public function attributesMessages(array $messages);

	/**
	 * Set the default message for a rule.
	 * @param  string $rule    rule name
	 * @return string
	 */
	public function getRuleMessage($rule);

	/**
	 * Get an instance of a rule.
	 * @param  string $rule    rule name
	 * @param  array  $params [description]
	 * @return Rule
	 */
	public function getRule($rule, array $params);

	/**
	 * Get the RulesRegistry instance.
	 * @return RulesRegistryInterface
	 */
	public function getRegistry();

	/**
	 * Set the RulesRegistry instance.
	 * @param RulesRegistryInterface $registry
	 * @return ValidatorInterface       $this
	 */
	public function setRegistry(RulesRegistryInterface $registry);

	/**
	 * Set the input for validation.
	 * @param mixed $input
	 */
	public function setInput($input);

	/**
	 * Get the input.
	 * @return mixed
	 */
	public function getInput();

	/**
	 * Set the parent validator.
	 * @param ValidatorInterface $parent
	 * @return ValidatorInterface       $this
	 */
	public function setParent(ValidatorInterface $parent);

	/**
	 * Set the validator name.
	 * @param string $name
	 * @return ValidatorInterface       $this
	 */
	public function setName($name);

	/**
	 * Raise an exception if there is an error for the given input.
	 * @param  mixed $input
	 * @throws ValidatorException If there is an error for the given input.
	 * @return null
	 */
	public function assert($input);

	/**
	 * Check if a rule validates the input.
	 * @param  string $rule    rule name
	 * @return boolean         true if the input is valid, otherwise false.
	 */
	public function validRule($rule);

	/**
	 * Check the input is valid.
	 * @param  mixed $input
	 * @return boolean        true is the input is valid, false otherwise.
	 */
	public function valid($input=null);

	/**
	 * Return the errors report.
	 * @param  mixed $input
	 * @return Report
	 */
	public function errors($input=null);

	/**
	 * Return the raw errors.
	 * @param  mixed $input
	 * @return array
	 */
	public function _errors($input=null);

	/**
	 * Get the validator name.
	 * @return string
	 */
	public function getName();

	/**
	 * Format parameters before passing them to the message.
	 * @param  array        $formatParameters
	 * @return ValidatorInterface    $this
	 */
	public function formatParameters($formatParameters);

	/**
	 * Set a parameter.
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value);

	/**
	 * Get a parameter.
	 * @param  string $key
	 * @return mixed
	 */
	public function get($key);
}