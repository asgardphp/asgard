<?php
namespace Asgard\Validation;

/**
 * Contains the rules for validation.
 */
interface RulesRegistryInterface {
	/**
	 * Set the default message of a rule.
	 * @param  string $rule    rule name
	 * @param  string $message
	 * @return RulesRegistryInterface   $this
	 */
	public function message($rule, $message);

	/**
	 * Set an array of rules messages.
	 * @param  array  $rules
	 * @return RulesRegistryInterface $this
	 */
	public function messages(array $rules);

	/**
	 * Get the default message of a rule.
	 * @param  string $rule rule name
	 * @return string
	 */
	public function getMessage($rule);

	/**
	 * Register a rule.
	 * @param  string $rule         rule name
	 * @param  callable|string      $object
	 * @return RulesRegistryInterface        $this
	 */
	public function register($rule, $object);

	/**
	 * Register a namespace.
	 * @param  string $namespace
	 * @return RulesRegistryInterface          $this
	 */
	public function registerNamespace($namespace);

	/**
	 * Get a rule instance.
	 * @param  string $rule   rule name
	 * @param  array $params rule parameters
	 * @return Rule
	 */
	public function getRule($rule, $params=[]);

	/**
	 * Get the name of a rule.
	 * @param  Rule $rule rule object
	 * @return string       rule name
	 */
	public function getRuleName($rule);
}