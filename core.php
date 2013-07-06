<?php
if(version_compare(PHP_VERSION, '5.3.0') < 0)
	die('You need PHP ≥ 5.3');

/* ENV */
define('_START_', time()+microtime());
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);
define('_DIR_', getcwd().'/'); #todo move it to index.php?
define('_VENDOR_DIR_', _DIR_.'vendor/'); #todo move it to index.php?
set_include_path(get_include_path() . PATH_SEPARATOR . _DIR_);
define('_WEB_DIR_', _DIR_.'web/');#todo: remove..

/* UTILS */
function d() {
	while(ob_get_level()){ ob_end_clean(); }
		
	if(php_sapi_name() != 'cli')
		echo '<pre>';
	foreach(func_get_args() as $arg)
		var_dump($arg);
	if(php_sapi_name() != 'cli')
		echo '</pre>';
	
	\Coxis\Core\Error::print_backtrace('', debug_backtrace());
	exit();
}
if(!function_exists('getallheaders')) { 
	function getallheaders() { 
		$headers = ''; 
		foreach($_SERVER as $name => $value)
			if(substr($name, 0, 5) == 'HTTP_')
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
		return $headers; 
	} 
} 
function coxis_array_merge(&$a,$b){
    foreach($b as $child=>$value) {
        if(isset($a[$child])) {
            if(is_array($a[$child]) && is_array($value))
                coxis_array_merge($a[$child],$value);
        }
        else
            $a[$child]=$value;
    }
}
function __($key, $params=array()) {
	return \Context::get('locale')->translate($key, $params);
}
function is_function($f) {
    return (is_object($f) && ($f instanceof \Closure));
}

ob_start();

/* CORE/LIBS */
require_once _VENDOR_DIR_.'coxis/utils/Tools.php';
require_once _VENDOR_DIR_.'coxis/core/Coxis.php';
require_once _VENDOR_DIR_.'coxis/core/IoC.php';
require_once _VENDOR_DIR_.'coxis/core/Context.php';
require_once _VENDOR_DIR_.'coxis/utils/NamespaceUtils.php';
require_once _VENDOR_DIR_.'coxis/core/Importer.php';
require_once _VENDOR_DIR_.'coxis/core/Autoloader.php';

spl_autoload_register(array('Coxis\Core\Autoloader', 'loadClass'));
Autoloader::preloadDir(_VENDOR_DIR_.'coxis/core/');
foreach(Coxis::$facades as $facade=>$class)
	Autoloader::map(strtolower($facade), _VENDOR_DIR_.'coxis/core/facades/'.$facade.'.php');

\Coxis\Utils\Timer::start();

/* ERRORS/EXCEPTIONS */
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
	if(defined('_ENV_') && Config::get('errno')!=null && $errno <= Config::get('errno'))
		return;
	throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
});
set_exception_handler(function ($e) {
	\Coxis\Core\Coxis::getExceptionResponse($e)->send();
});
register_shutdown_function(function () {
	\Coxis\Core\Coxis::setDefaultEnvironment();
	if(!\Config::get('no_shutdown_error')) {
		chdir(_DIR_);//wtf?
		#todo get the full backtrace for shutdown errors
		if($e=error_get_last()) {
			if($e['type'] == 1) {
				while(ob_get_level()){ ob_end_clean(); }
				$response = \Coxis\Core\Error::report("($e[type]) $e[message]<br>
					$e[file] ($e[line])", array(array('file'=>$e['file'], 'line'=>$e['line'])));
				$response->send(false);
			}
		}
	}
	if(\Config::get('profiler'))
		Profiler::report();
});
\Coxis\Utils\Profiler::checkpoint('End of coxis.php');
