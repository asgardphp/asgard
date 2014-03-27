<?php
if(version_compare(PHP_VERSION, '5.4.0') < 0)
	die('You need PHP ≥ 5.4');

define('_ASGARD_START_', time()+microtime());
set_include_path(get_include_path() . PATH_SEPARATOR . _DIR_);

require_once _VENDOR_DIR_.'autoload.php'; #composer autoloader

\Asgard\Core\ErrorHandler::initialize();

ob_start();