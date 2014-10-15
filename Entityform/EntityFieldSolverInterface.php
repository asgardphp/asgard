<?php
namespace Asgard\Entityform;

/**
 * Solve form fields from entity properties.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface EntityFieldSolverInterface {
	/**
	 * Add a nested solver.
	 * @param EntityFieldSolverInterface $solver
	 */
	public function addSolver(EntityFieldSolverInterface $solver);

	/**
	 * Add a callback.
	 * @param callable $cb
	 */
	public function add(callable $cb);

	/**
	 * Add a "many" callback.
	 * @param callable $cb
	 */
	public function addMany(callable $cb);

	/**
	 * Solve a property.
	 * @param  \Asgard\Entity\Property $property
	 * @return \Asgard\Form\Field|\Asgard\Form\GroupInterface
	 */
	public function solve(\Asgard\Entity\Property $property);

	/**
	 * Actually solve a "single" property.
	 * @param  \Asgard\Entity\Property $property
	 * @return \Asgard\Form\Field
	 */
	public function doSolve(\Asgard\Entity\Property $property);

	/**
	 * Actually solve a "many" property.
	 * @param  \Asgard\Entity\Property $property
	 * @return \Asgard\Form\GroupInterface
	 */
	public function doSolveMany(\Asgard\Entity\Property $property);
}