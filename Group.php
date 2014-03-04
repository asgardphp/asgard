<?php
namespace Asgard\Form;

class Group extends AbstractGroup {
	function __construct($fields, $dad=null, $name=null, $data=null, $files=null) {	
		$this->dad = $dad;
		$this->data = $data;
		$this->files = $files;
		$this->groupName = $name;
		$this->addFields($fields);
	}
}