<?php
namespace Asgard\Http;

class CookieManager {
	public function set($what, $value=null, $time=null, $path='/') {#, $time=null, $path='/'
		if(!headers_sent()) {
			if($time === null)
				$time = time()+3600*24*365;
			setcookie($what, $value, $time, $path);
		}
	}
	
	public function remove($what, $path='/') {
		if(!headers_sent())
			setcookie($what, false, -10000, $path);
	}
}