<?php
namespace Asgard\Entityform;

/**
 * Solve form fields from entity properties.
 */
class EntityFieldsSolver implements EntityFieldsSolverInterface {
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
	 * Array of callbacks to solve fields from "many" properties.
	 * @var array
	 */
	protected $callbacksMany = [];

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
	 * {@inheritDoc}
	 */
	public function addSolver(EntityFieldsSolverInterface $solver) {
		$this->solvers[] = $solver;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function add(callable $cb) {
		$this->callbacks[] = $cb;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMany(callable $cb) {
		$this->callbacksMany[] = $cb;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function solve(\Asgard\Entity\Property $property) {
		if($property->get('many'))
			return $this->doSolveMany($property);
		else
			return $this->doSolve($property);
	}

	/**
	 * {@inheritDoc}
	 */
	public function doSolve(\Asgard\Entity\Property $property) {
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
	 * {@inheritDoc}
	 */
	public function doSolveMany(\Asgard\Entity\Property $property) {
		foreach(array_reverse($this->callbacksMany) as $cb) {
			if(($res = $cb($property)) !== null)
				return $res;
		}
		foreach(array_reverse($this->solvers) as $cb) {
			if($cb instanceof static && ($res = $cb->doSolveMany($property)) !== null)
				return $res;
		}

		return new \Asgard\Form\DynamicGroup;
	}
}