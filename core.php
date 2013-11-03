<?php
if(version_compare(PHP_VERSION, '5.3.0') < 0)
	die('You need PHP ≥ 5.3');

/* ENV */
define('_START_', time()+microtime());
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);
set_include_path(get_include_path() . PATH_SEPARATOR . _DIR_);

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
                coxis_array_merge($a[$child], $value);
        }
        else
            $a[$child] = $value;
    }
}
function __($key, $params=array()) {
	return \Coxis\Core\Context::get('locale')->translate($key, $params);
}
function is_function($f) {
    return (is_object($f) && ($f instanceof \Closure));
}

/* CORE/LIBS */
require_once _COXIS_DIR_.'utils/Tools.php';
require_once _CORE_DIR_.'Coxis.php';
require_once _CORE_DIR_.'IoC.php';
require_once _CORE_DIR_.'Context.php';
require_once _COXIS_DIR_.'utils/NamespaceUtils.php';
require_once _CORE_DIR_.'Importer.php';
require_once _CORE_DIR_.'Autoloader.php';

require _VENDOR_DIR_.'autoload.php'; #composer autoloader
spl_autoload_register(array('Coxis\Core\Autoloader', 'loadClass')); #coxis autoloader

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
			// if($e['type'] == 1) { #todo don't catch max file upload size
				while(ob_get_level()){ ob_end_clean(); }
				$response = \Coxis\Core\Error::report("($e[type]) $e[message]<br>
					$e[file] ($e[line])", array(array('file'=>$e['file'], 'line'=>$e['line'])));
				$response->send(false);
			// }
		}
	}
	if(\Config::get('profiler'))
		Profiler::report();
});

ob_start();
\Coxis\Core\Autoloader::preloadDir(_CORE_DIR_);

\Coxis\Core\Facades::inst()->register('Importer', '\Coxis\Core\Facades\Importer');
\Coxis\Core\Facades::inst()->register('Router', '\Coxis\Core\Facades\Router');
\Coxis\Core\Facades::inst()->register('Config', '\Coxis\Core\Facades\Config');
\Coxis\Core\Facades::inst()->register('Response', '\Coxis\Core\Facades\Response');
\Coxis\Core\Facades::inst()->register('Memory', '\Coxis\Core\Facades\Memory');
\Coxis\Core\Facades::inst()->register('Flash', '\Coxis\Core\Facades\Flash');
\Coxis\Core\Facades::inst()->register('Validation', '\Coxis\Core\Facades\Validation');
\Coxis\Core\Facades::inst()->register('ModelsManager', '\Coxis\Core\Facades\ModelsManager');
\Coxis\Core\Facades::inst()->register('Hook', '\Coxis\Core\Facades\Hook');
\Coxis\Core\Facades::inst()->register('Locale', '\Coxis\Core\Facades\Locale');
\Coxis\Core\Facades::inst()->register('Request', '\Coxis\Core\Facades\Request');
\Coxis\Core\Facades::inst()->register('URL'	, '\Coxis\Core\Facades\URL');
\Coxis\Core\Facades::inst()->register('Session', '\Coxis\Core\Facades\Session');
\Coxis\Core\Facades::inst()->register('Get', '\Coxis\Core\Facades\Get');
\Coxis\Core\Facades::inst()->register('Post', '\Coxis\Core\Facades\Post');
\Coxis\Core\Facades::inst()->register('File', '\Coxis\Core\Facades\File');
\Coxis\Core\Facades::inst()->register('Cookie', '\Coxis\Core\Facades\Cookie');
\Coxis\Core\Facades::inst()->register('Server', '\Coxis\Core\Facades\Server');

\Coxis\Core\Facades::inst()->register('CLIRouter', 'Coxis\Cli\Facades\CLIRouter');
\Coxis\Core\Facades::inst()->register('HTML', 'Coxis\Utils\Facades\HTML');

\Coxis\Utils\Profiler::checkpoint('End of coxis.php');
