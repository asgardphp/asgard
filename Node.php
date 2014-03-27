<?php
namespace Asgard\Xpath;

class Node {
	protected $domnode;
	protected $xpath;

	public function __construct($domnode) {
		if(!$domnode)
			return;
		$this->domnode = $domnode;
		$this->xpath = static::nodeToXpath($domnode);
	}

	public function prev() {
		$prev = $this->domnode;
		while(true) {
			$prev = $prev->previousSibling;
			if(!$prev)
				return;
			if($prev->nodeType == XML_ELEMENT_NODE)
				return new Node($prev);
		}
	}

	public function next() {
		$next = $this->domnode;
		while(true) {
			$next = $next->nextSibling;
			if(!$next)
				return;
			if($next->nodeType == XML_ELEMENT_NODE)
				return new Node($next);
		}
	}

	/**
	* Convert a DOMNode object to a DOMXPath object
	*/
	protected static function nodeToXpath($domnode) {
		$dom = new \DOMDocument();
		$dom->formatOutput = true;
		$node = $dom->importNode($domnode, true);
		$dom->appendChild($node);
		return new \DOMXPath($dom);
	}

	/**
	* Return the variable xpath (DOMXPath)
	*/
	public function getXpath() {
		return $this->xpath;
	}

	/**
	* Return the variable domnode (DOMNode)
	*/
	public function getNode() {
		return $this->domnode;
	}

	/**
	* Return an attribute of the node
	*/
	public function getAttribute($attr) {
		if($this->domnode === null)
			return null;
		return $this->domnode->getAttribute($attr);
	}

	/**
	* Return the inner html of a DOMNode object
	*/
	protected static function getInnerHTML($node) {
		if(!$node)
			return null;
		$innerHTML= ''; 
		$children = $node->childNodes; 
		foreach ($children as $child)
			$innerHTML .= $child->ownerDocument->saveHTML($child);
		return $innerHTML; 
	}

	/**
	* Return the html for a specific children
	*/
	public function html($path=null, $pos=0) {
		if($this->xpath === null)
			return null;
		if(!$path)
			return trim(static::getInnerHTML($this->domnode));
		else {
			$item = $this->item($path, $pos);
			if($item === null)
				return null;
			return $item->html();
		}
	}

	/**
	* Return the inner xml of a DOMNode object
	*/
	protected static function getInnerXML($node) {
		if($this->xpath === null)
			return null;
		$innerHTML= ''; 
		$children = $node->childNodes; 
		foreach ($children as $child)
			$innerHTML .= $child->ownerDocument->saveXML($child);
		return $innerHTML; 
	}

	/**
	* Return the inner xml for a specific children
	*/
	public function xml($path=null, $pos=0) {
		if($this->xpath === null)
			return null;
		if(!$path)
			return trim(static::getInnerXML($this->domnode));
		else {
			$item = $this->item($path, $pos);
			if($item === null)
				return null;
			return $item->xml();
		}
	}

	/**
	* Return the text for a specific children
	*/
	public function text($path=null, $pos=0) {
		if($this->xpath === null)
			return null;
		if(!$path)
			return trim($this->domnode->nodeValue);
		else {
			$item = $this->item($path, $pos);
			if($item === null)
				return null;
			return $item->text();
		}
	}

	/**
	* Return a specific children
	*/
	public function item($path, $pos=0) {
		if($this->xpath === null)
			return new Node(null);
		return new Node($this->xpath->evaluate($path)->item($pos));
	}

	/**
	* Return an array of children
	*/
	public function items($path) {
		if($this->xpath === null)
			return array();
		$r = array();
		$nodes = $this->xpath->evaluate($path);
		foreach($nodes as $n)
			$r[] = new Node($n);
		return $r;
	}
}