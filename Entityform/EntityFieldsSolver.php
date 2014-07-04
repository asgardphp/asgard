<?php
namespace Asgard\Entityform;

class EntityFieldsSolver {
	protected $solvers = [];
	protected $callbacks = [];
	protected $callbacksMultiple = [];

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

	public function addSolver($solver) {
		$this->solvers[] = $solver;
		return $this;
	}

	public function add($cb) {
		$this->callbacks[] = $cb;
		return $this;
	}

	public function addMultiple($cb) {
		$this->callbacksMultiple[] = $cb;
		return $this;
	}

	public function solve($property) {
		if($property->get('multiple'))
			return $this->doSolveMultiple($property);
		else
			return $this->doSolve($property);
	}

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