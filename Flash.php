<?php
namespace Asgard\Utils;

class Flash {
	protected $messages = array('success' => array(), 'error' => array());

	function __construct() {
		if(\Asgard\Core\App::get('session')->has('messages'))
			$this->messages = \Asgard\Core\App::get('session')->get('messages');
	}

	protected function persist() {
		\Asgard\Core\App::get('session')->set('messages', $this->messages);
	}

	public function addSuccess($message) {
		if(is_array($message))
			$this->messages['success'] = array_merge($this->messages['success'], $message);
		else
			$this->messages['success'][] = $message;
			
		$this->persist();
		return true;
	}
	
	public function addError($message) {
		if(is_array($message))
			$this->messages['error'] = array_merge($this->messages['error'], $message);
		else
			$this->messages['error'][] = $message;
			
		$this->persist();
		return true;
	}
	
	public function showAll($cat=null) {
		$this->showSuccess($cat);
		$this->showError($cat);
	}
	
	public function showSuccess($cat=null) {
		if($cat)
			$messages = isset($this->messages['success'][$cat]) ? Tools::flateArray($this->messages['success'][$cat]):array();
		else
			$messages = Tools::flateArray($this->messages['success']);
		foreach($messages as $msg)
			echo '<div class="message success"><p>'.$msg.'</p></div>'."\n";
		if($cat)
			unset($this->messages['success'][$cat]);
		else
			$this->messages['success'] = array();	
		$this->persist();
	}
	
	public function showError($cat=null) {
		if($cat)
			$messages = isset($this->messages['error'][$cat]) ? Tools::flateArray($this->messages['error'][$cat]):array();
		else
			$messages = Tools::flateArray($this->messages['error']);
		foreach($messages as $msg)
			echo '<div class="message errormsg"><p>'.$msg.'</p></div>'."\n";
		if($cat)
			unset($this->messages['error'][$cat]);
		else
			$this->messages['error'] = array();	
		$this->persist();
	}
}