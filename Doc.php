<?php
namespace Asgard\Xpath;

class Doc {
	protected $code;
	protected $xpath;

	public function __construct($code, $type='html') {
		$this->code = $code;
		$this->xpath = static::toXpath($code, $type);
	}

	/**
	* Convert xml/html code to a DOMXpath object
	*/
	protected static function toXpath($code, $type='html') {
		$doc = new \DOMDocument();
		if ($type == 'xml')
			@$doc->loadXML($code);
		else
			@$doc->loadHTML($code);
		return new \DOMXPath($doc);
	}

	/**
	* Return the xpath variable
	*/
	public function getXpath() {
		return $this->xpath;
	}

	/**
	* Return the document code
	*/
	public function getCode() {
		return $this->code;
	}

	/**
	* Return the html code for a specific children
	*/
	public function html($path=null, $pos=0) {
		if($path === null)
			return $this->code;
		return $this->item($path, $pos)->html();
	}

	/**
	* Return text for a specific children
	*/
	public function text($path, $pos=0) {
		return $this->item($path, $pos)->text();
	}

	/**
	* Return the first Node object from xpath
	*/
	public function item($path, $pos=0) {
		return new Node($this->xpath->evaluate($path)->item($pos));
	}

	/**
	* Return an array of node objects from xpath
	*/
	public function items($path) {
		$r = array();
		$nodes = $this->xpath->evaluate($path);
		foreach($nodes as $n)
			$r[] = new Node($n);
		return $r;
	}
}