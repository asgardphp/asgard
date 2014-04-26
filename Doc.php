<?php
namespace Asgard\Xpath;

/**
 * Xpath handler for the whole document.
 * 
 * @author Michel Hognerud <michel@hognerud.net>
*/
class Doc {
	protected $code;
	protected $xpath;
	
	/**
	 * Constructor
	 * 
	 * @param String code The html/xml code to be parsed.
	 * @param String type html|xml
	*/
	public function __construct($code, $type='html') {
		$this->code = $code;
		$this->xpath = static::toXpath($code, $type);
	}
	
	/**
	 * Returns the xpath object.
	 * 
	 * @return \DOMXPath
	*/
	public function getXpath() {
		return $this->xpath;
	}
	
	/**
	 * Returns the document code.
	 * 
	 * @return string The parsed code.
	*/
	public function getCode() {
		return $this->code;
	}
	
	/**
	 * Returns the html code for a specific children
	 * 
	 * @param string path Path to the node.
	 * @param integer pos Element position.
	 * 
	 * @return string The html code of the node.
	*/
	public function html($path=null, $pos=0) {
		if($path === null)
			return $this->code;
		return $this->item($path, $pos)->html();
	}
	
	/**
	 * Returns text for a specific children.
	 * 
	 * @param string path Path to the node.
	 * @param integer pos Element position.
	 * 
	 * @return string The text of the node.
	*/
	public function text($path, $pos=0) {
		return $this->item($path, $pos)->text();
	}
	
	/**
	 * Returns the first Node object from xpath.
	 * 
	 * @param string path Path to the node.
	 * @param integer pos Element position.
	 * 
	 * @return \Asgard\Xpath\Node
	*/
	public function item($path, $pos=0) {
		return new Node($this->xpath->evaluate($path)->item($pos));
	}
	
	/**
	 * Returns an array of node objects from xpath.
	 * 
	 * @param string path Path to the nodes.
	 * 
	 * @return array Array of \Asgard\Xpath\Node objects.
	*/
	public function items($path) {
		$r = array();
		$nodes = $this->xpath->evaluate($path);
		foreach($nodes as $n)
			$r[] = new Node($n);
		return $r;
	}
	
	/**
	 * Convert xml/html code to a DOMXpath object.
	 * 
	 * @param String code The html/xml code to be parsed.
	 * @param String type html|xml
	 * 
	 * @return \DOMXPath
	*/
	protected static function toXpath($code, $type='html') {
		$doc = new \DOMDocument();
		if ($type == 'xml')
			@$doc->loadXML($code);
		else
			@$doc->loadHTML($code);
		return new \DOMXPath($doc);
	}
}