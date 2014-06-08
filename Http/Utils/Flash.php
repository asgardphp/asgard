<?php
namespace Asgard\Http\Utils;

class Flash {
	protected $messages = array();
	protected $session;
	protected $cb;

	public function __construct($session) {
		$this->session = $session;
		if($session->has('messages'))
			$this->messages = $session['messages'];
	}

	protected function persist() {
		$this->session['messages'] = $this->messages;
	}

	public function addSuccess($message) {
		return $this->add('success', $message);
	}

	public function addError($message) {
		return $this->add('error', $message);
	}

	public function addInfo($message) {
		return $this->add('info', $message);
	}

	public function addWarning($message) {
		return $this->add('warning', $message);
	}
	
	public function add($type, $message) {
		if(is_array($message))
			$this->messages[$type] = array_merge($this->messages[$type], $message);
		else
			$this->messages[$type][] = $message;
			
		$this->persist();
		return true;
	}
	
	public function showAll($cat=null) {
		foreach($this->messages as $type=>$messages)
			$this->show($type, $cat);
	}

	public function show($type, $cat=null, $cb=null) {
		if($cat)
			$messages = isset($this->messages[$type][$cat]) ? \Asgard\Utils\Tools::flateArray($this->messages[$type][$cat]):array();
		else
			$messages = isset($this->messages[$type]) ? \Asgard\Utils\Tools::flateArray($this->messages[$type]):array();
		foreach($messages as $msg) {
			if($cb)
				echo $cb($msg, $type);
			else
				echo '<div class="flash '.$type.'">'.$msg.'</div>'."\n";
		}
		if($cat)
			unset($this->messages[$type][$cat]);
		else
			$this->messages[$type] = array();	
		$this->persist();
	}
}