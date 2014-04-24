<?php
require_once 'paths.php';
require_once _VENDOR_DIR_.'autoload.php'; #composer autoloader
\Asgard\Core\App::loadDefaultApp();

if(file_exists('config/config.php'))
	echo 'File "config/config.php" already exists.'."\n";
else {
	$config = file_get_contents(__DIR__.'/config.php.sample');
	$key = \Asgard\Utils\Tools::randStr(10);
	$config = str_replace('_KEY_', $key, $config);

	if(\Asgard\Utils\FileManager::put('config/config.php', $config) !== false)
		echo 'Configuration created with success.'."\n";
	else
		echo 'Configuration creation failed.'."\n";			
}