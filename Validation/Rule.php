<?php
namespace Asgard\Validation;

/**
 * Rule parent class.
 * @author Michel Hognerud <michel@hognerud.com>
 */
abstract class Rule {
	/**
	 * If true, the rule must be checked against all inputs of an array.
	 * @var boolean
	 */
	protected $handleEach = false;

	/**
	 * Groups.
	 * @var array|null
	 */
	protected $groups;

	/**
	 * Constructor.
	 */
	public function __construct() {}

	/**
	 * Perform the validation.
	 * @param  mixed     $input
	 * @param  InputBag  $parentInput
	 * @param  ValidatorInterface $validator
	 * @return boolean
	 */
	abstract public function validate($input, InputBag $parentInput, ValidatorInterface $validator);

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

	/**
	 * Set the groups.
	 * @param array|string|null $groups
	 */
	public function setGroups($groups=null) {
		if(!is_array($groups) && $groups !== null)
			$groups = [$groups];
		$this->groups = $groups;
		return $this;
	}

	/**
	 * Return the groups.
	 * @return array
	 */
	public function getGroups() {
		return $this->groups;
	}

	/**
	 * Check if the validator belongs to some groups.
	 * @param  array     $groups
	 * @param  Validator $validator
	 * @return boolean
	 */
	public function belongsToGroups(array $groups, Validator $validator) {
		if($this->groups === null)
			return $validator->belongsToGroups($groups);
		else {
			foreach($groups as $group) {
				if(in_array($group, $this->groups))
					return true;
			}
			return false;
		}
	}
}