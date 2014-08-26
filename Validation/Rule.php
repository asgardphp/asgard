<?php
namespace Asgard\Validation;

/**
 * Rule parent class.
 */
abstract class Rule {
	/**
	 * If true, the rule must be checked against all inputs of an array.
	 * @var boolean
	 */
	protected $handleEach = false;

	/**
	 * Constructor.
	 */
	public function __construct() {}

	/**
	 * Format parameters before being passed to the error message.
	 * @param  array  $params rule parameters
	 * @return null
	 */
	public function formatParameters(array &$params) {}

	/**
	 * Return the error message.
	 * @return string
	 */
	public function getMessage() {}

	/**
	 * Check if the rule must handle each input.
	 * @return boolean
	 */
	public function isHandlingEach() { return $this->handleEach; }

	/**
	 * Set handleEach, to handle each input of an array.
	 * @param  boolean $handleEach
	 * @return null
	 */
	public function handleEach($handleEach) { $this->handleEach = $handleEach; }
}