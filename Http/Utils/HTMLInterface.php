<?php
namespace Asgard\Http\Utils;

/**
 * HTML helper.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface HTMLInterface {
	/**
	 * Start buffer to write code.
	 */
	public function codeStart();

	/**
	 * End buffer to write code.
	 */
	public function codeEnd();

	/**
	 * Get page title.
	 * @return string
	 */
	public function getTitle();

	/**
	 * Get page description.
	 * @return string
	 */
	public function getDescription();

	/**
	 * Get page keywords
	 * @return string
	 */
	public function getKeywords();

	/**
	 * Set page title.
	 * @param string $title
	 */
	public function setTitle($title);

	/**
	 * Set page description.
	 * @param string $description
	 */
	public function setDescription($description);

	/**
	 * Set page keywords.
	 * @param string $keywords
	 */
	public function setKeywords($keywords);

	/**
	 * Print the title.
	 */
	public function printTitle();

	/**
	 * Print the description.
	 */
	public function printDescription();

	/**
	 * Print the keywords.
	 */
	public function printKeywords();

	/**
	 * Include a JS file.
	 * @param  string $js
	 */
	public function includeJS($js);

	/**
	 * Include a CSS file.
	 * @param  string $css
	 */
	public function includeCSS($css);

	/**
	 * Include JS code.
	 * @param  string $js
	 */
	public function codeJS($js);

	/**
	 * Include CSS code.
	 * @param  string $css
	 */
	public function codeCSS($css);

	/**
	 * Include code.
	 * @param  string $code
	 */
	public function code($code);

	/**
	 * Print JS files.
	 */
	public function printJSInclude();

	/**
	 * Print CSS files.
	 */
	public function printCSSInclude();

	/**
	 * Print JS code.
	 */
	public function printJSCode();

	/**
	 * Print CSS code.
	 */
	public function printCSSCode();

	/**
	 * Print code.
	 */
	public function printCode();

	/**
	 * Print all.
	 */
	public function printAll();
}