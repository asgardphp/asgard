<?php
namespace Asgard\Http\Browser;

/**
 * Form field.
 */
class Field {
	/**
	 * Field value.
	 * @var string
	 */
	protected $value;
	/**
	 * Field choices.
	 * @var array
	 */
	protected $choices = [];
	/**
	 * Field type.
	 * @var [type]
	 */
	protected $type;

	/**
	 * Constructor.
	 * @param \DOMElement $node
	 */
	function __construct(\DOMElement $node) {
		$nodeName = $node->nodeName;

		switch($nodeName) {
			case 'input':
				$inputType = $node->getAttribute('type');
				$inputValue = $node->getAttribute('value');
				$this->type = $inputType;
				switch($inputType) {
					case 'text':
					case 'password':
					case 'hidden':
						$this->value = $inputValue;
						break;
					case 'submit':
					case 'image':
						$this->value = $inputValue;
						break;
					case 'radio':
					case 'checkbox':
						$this->addChoice($node);
						break;
					case 'file':
						break;
				}
				break;
			case 'select':
				$this->type = $nodeName;
				$multiple = $node->getAttribute('multiple') == 'multiple';

				$document = new \DOMDocument('1.0', 'UTF-8');
				$node = $document->importNode($node, true);
				$root = $document->appendchild($document->createElement('_root'));
				$root->appendchild($node);
				$xpath = new \DOMXPath($document);
				
				foreach($xpath->query('descendant::option', $root) as $option_node) {
					$value = $option_node->getAttribute('value');
					$this->choices[] = $value;
					if($option_node->getAttribute('selected') == 'selected') {
						if($multiple)
							$this->value[] = $value;
						else
							$this->value = $value;
					}
				}
				break;
			case 'textarea':
				$this->type = $nodeName;
				$nodeValue = $node->nodeValue;
				$this->value = $nodeValue;
				break;
		}
	}

	/**
	 * Add a choice to the field.
	 * @param \DOMElement $node
	 */
	public function addChoice(\DOMElement $node) {
		if($node->nodeName != 'input')
			return;
		$inputValue = $node->getAttribute('value');
		$this->choices[] = $inputValue;
		switch($node->getAttribute('type')) {
			case 'radio':
				if($node->getAttribute('checked') == 'checked')
					$this->value = $inputValue;
				break;
			case 'checkbox':
				if($node->getAttribute('checked') == 'checked')
					$this->value = $inputValue;
				break;
		}
	}

	/**
	 * Get the field's value.
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Set the field's value.
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * Get the field's type.
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
}

/**
 * Form parser.
 * Credit to Symfony.
 */
class FormParser {
	/**
	 * Fields.
	 * @var array
	 */
	protected $fields = [];
	/**
	 * Submit button xpath.
	 * @var string
	 */
	protected $submit = null;

	/**
	 * Check if it contains a field.
	 * @param  string  $name
	 * @return boolean
	 */
	public function has($name) {
		return isset($this->fields[$name]);
	}

	/**
	 * Return a field.
	 * @param  string $name
	 * @return Field
	 */
	public function get($name) {
		return $this->fields[$name];
	}

	/**
	 * Set a field.
	 * @param string $name
	 * @param Field $value
	 */
	public function set($name, Field $value) {
		$this->fields[$name] = $value;
	}

	/**
	 * Add a field.
	 * @param \DOMElement $node
	 */
	public function add(\DOMElement $node) {
		$name = $node->getAttribute('name');
		if($this->has($name))
			$this->get($name)->addChoice($node);
		else
			$this->set($name, new Field($node));
	}

	/**
	 * Get a field path.
	 * @param  string $name
	 * @return string
	 */
	protected function getPath($name) {
		$path = [];
		$matches = null;
		preg_match('/^([^\[]+)/', $name, $matches);
		$path[] = $matches[0];
		preg_match_all('/\[([^\]]*)\]/', $name, $matches);
		$path = array_merge($path, $matches[1]);

		return $path;
	}

	/**
	 * Get form values.
	 * @return array
	 */
	public function values() {
		$res = [];
		foreach($this->fields as $name=>$field) {
			$value = $field->getValue();
			if($value === null)
				continue;
			if(($field->getType() == 'image' || $field->getType() == 'submit') && $name !== $this->submit)
				continue;

			$path = $this->getPath($name);

			$arr =& $res;
			$key = array_pop($path);
			
			foreach($path as $parent)
				$arr =& $arr[$parent];
			if(!$key)
				$arr[] = $value;
			else
				$arr[$key] = $value;
		}
		return $res;
	}

	/**
	 * Simulate the click on a form button.
	 * @param  string $submit
	 */
	public function clickOn($submit) {
		$this->submit = $submit;
	}

	/**
	 * Parse the HTML form.
	 * @param  string $html
	 * @param  string $xpath xpath to form
	 */
	public function parse($html, $xpath) {
		$doc = new \DOMDocument();
		$doc->loadHTML($html);
		$domxpath = new \DOMXPath($doc);
		$node = $domxpath->evaluate($xpath)->item(0);

		$document = new \DOMDocument('1.0', 'UTF-8');
		$node = $document->importNode($node, true);
		$root = $document->appendchild($document->createElement('_root'));
		$root->appendchild($node);
		$xpath = new \DOMXPath($document);

		foreach ($xpath->query('descendant::input | descendant::textarea | descendant::select', $root) as $node) {
			if (!$node->hasAttribute('name'))
				continue;
			$this->add($node);
		}
	}
}