<?php
namespace Asgard\Templating;

/**
 * Interface for template engines
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface TemplateEngineInterface {
	/**
	 * Check if the template file exists.
	 * @param  string $template template name
	 * @return boolean
	 */
	public function templateExists($template);

	/**
	 * Return the template file.
	 * @param  string $template template name
	 * @return string
	 */
	public function getTemplateFile($template);

	/**
	 * Create a new template instance
	 * @return TemplateInterfacce
	 */
	public function createTemplate();
}