<?php
namespace Asgard\Templating;

/**
 * Template engine factory interface.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface TemplateEngineFactoryInterface {
	/**
	 * Create a new instance.
	 * @return TemplateEngineInterface
	 */
	public function create();
}