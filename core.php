<?php
if(version_compare(PHP_VERSION, '5.3.0') < 0)
	die('You need PHP ≥ 5.3');

define('_COXIS_START_', time()+microtime());
set_include_path(get_include_path() . PATH_SEPARATOR . _DIR_);

/* CORE CLASSES */
require_once _CORE_DIR_.'Coxis.php';
require_once _CORE_DIR_.'App.php';
require_once _CORE_DIR_.'Importer.php';
require_once _CORE_DIR_.'Autoloader.php';
require_once _COXIS_DIR_.'utils/NamespaceUtils.php';
require_once _COXIS_DIR_.'utils/Tools.php';

require _VENDOR_DIR_.'autoload.php'; #composer autoloader
spl_autoload_register(array('Coxis\Core\Autoloader', 'loadClass')); #coxis autoloader

\Coxis\Core\ErrorHandler::initialize();

ob_start();