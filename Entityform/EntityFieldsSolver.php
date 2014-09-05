<?php
namespace Asgard\Entityform;

/**
 * Solve form fields from entity properties.
 */
class EntityFieldsSolver {
	/**
	 * Array of nested solvers.
	 * @var array
	 */
	protected $solvers = [];
	/**
	 * Array of callbacks to solve fields from properties.
	 * @var array
	 */
	protected $callbacks = [];
	/**
	 * Array of callbacks to solve fields from "multiple" properties.
	 * @var array
	 */
	protected $callbacksMultiple = [];

	/**
	 * Constructor.
	 * @param array $solvers
	 */
	public function __construct($solvers=[]) {
		$this->add(function($property) {
			$class = get_class($property);
			switch($class) {
				case 'Asgard\Entity\Properties\TextProperty':
				case 'Asgard\Entity\Properties\LongtextProperty':
				case 'Asgard\Entity\Properties\DoubleProperty':
				case 'Asgard\Entity\Properties\IntegerProperty':
				case 'Asgard\Entity\Properties\EmailProperty':
					return new \Asgard\Form\Fields\TextField;
				case 'Asgard\Entity\Properties\BooleanProperty':
					return new \Asgard\Form\Fields\BooleanField;
				case 'Asgard\Entity\Properties\DateProperty':
				case 'Asgard\Entity\Properties\DatetimeProperty':
					return new \Asgard\Form\Fields\DateField;
				case 'Asgard\Entity\Properties\FileProperty':
					return new \Asgard\Form\Fields\FileField;
			}
		});
		foreach($solvers as $solver)
			$this->addSolver($solver);
	}

	/**
	 * Add a nested solver.
	 * @param EntityFieldsSolver $solver
	 */
	public function addSolver($solver) {
		$this->solvers[] = $solver;
		return $this;
	}

	/**
	 * Add a callback.
	 * @param callback $cb
	 */
	public function add($cb) {
		$this->callbacks[] = $cb;
		return $this;
	}

	/**
	 * Add a "multiple" callback.
	 * @param callback $cb
	 */
	public function addMultiple($cb) {
		$this->callbacksMultiple[] = $cb;
		return $this;
	}

	/**
	 * Solve a property.
	 * @param  \Asgard\Entity\Property $property
	 * @return \Asgard\Form\Field
	 */
	public function solve($property) {
		if($property->get('multiple'))
			return $this->doSolveMultiple($property);
		else
			return $this->doSolve($property);
	}

	/**
	 * Actually solve a "single" property.
	 * @param  \Asgard\Entity\Property $property
	 * @return \Asgard\Form\Field
	 */
	public function doSolve($property) {
		foreach(array_reverse($this->callbacks) as $cb) {
			if(($res = $cb($property)) !== null)
				return $res;
		}
		foreach(array_reverse($this->solvers) as $cb) {
			if($cb instanceof static && ($res = $cb->doSolve($property)) !== null)
				return $res;
		}

		return new \Asgard\Form\Fields\TextField;
	}

	/**
	 * Actually solve a "multiple" property.
	 * @param  \Asgard\Entity\Property $property
	 * @return \Asgard\Form\Field
	 */
	public function doSolveMultiple($property) {
		foreach(array_reverse($this->callbacksMultiple) as $cb) {
			if(($res = $cb($property)) !== null)
				return $res;
		}
		foreach(array_reverse($this->solvers) as $cb) {
			if($cb instanceof static && ($res = $cb->doSolveMultiple($property)) !== null)
				return $res;
		}

		return new \Asgard\Form\DynamicGroup;
	}
}