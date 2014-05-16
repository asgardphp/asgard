<?php
namespace Asgard\Http\Inputs;

class COOKIE extends InputsBag {
	public function set($what, $value=null) {#, $time=null, $path='/'
		if(is_array($what)) {
			foreach($what as $k=>$v)
				call_user_func_array(array($this, 'set'), array($k, $v));
			return $this;
		}
		else {
			$args = func_get_args();
			$time = isset($args[2]) ? $args[2]:null;
			$path = isset($args[3]) ? $args[3]:'/';
			if($time===null)
				$time = time()+3600*24*365;
			setcookie($what, $value, $time, $path);
			return parent::set($what, $value);
		}
	}

	public function _set($what, $value=null) {#, $time=null, $path='/'
		if(is_array($what)) {
			foreach($what as $k=>$v)
				call_user_func_array(array($this, '_set'), array($k, $v));
			return $this;
		}
		else {
			$args = func_get_args();
			$time = isset($args[2]) ? $args[2]:null;
			$path = isset($args[3]) ? $args[3]:'/';
			if($time===null)
				$time = time()+3600*24*365;
			return parent::set($what, $value);
		}
	}
	
	public function remove($what, $path='/') {
		if(!headers_sent())
			setcookie($what, false, -10000, $path);
		return parent::remove($what);
	}
}