<?php
namespace Asgard\Entityform;

/**
 * Solve form fields from entity properties.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EntityFieldSolver implements EntityFieldSolverInterface {
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
				case 'Asgard\Entity\Property\StringProperty':
				case 'Asgard\Entity\Property\TextProperty':
				case 'Asgard\Entity\Property\DecimalProperty':
				case 'Asgard\Entity\Property\IntegerProperty':
				case 'Asgard\Entity\Property\EmailProperty':
					if($property->has('in'))
						return new \Asgard\Form\Field\SelectField;
					else
						return new \Asgard\Form\Field\TextField;
				case 'Asgard\Entity\Property\BooleanProperty':
					return new \Asgard\Form\Field\BooleanField;
				case 'Asgard\Entity\Property\DateProperty':
				case 'Asgard\Entity\Property\DatetimeProperty':
					return new \Asgard\Form\Field\DateField;
				case 'Asgard\Entity\Property\FileProperty':
					return new \Asgard\Form\Field\FileField;
			}
		});
		foreach($solvers as $solver)
			$this->addSolver($solver);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addSolver(EntityFieldSolverInterface $solver) {
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

		return new \Asgard\Form\Field\TextField;
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