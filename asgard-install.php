<?php
require_once 'paths.php';

if(file_exists('config/database.php'))
	echo 'File "config/database.php" already exists.'."\n";
else {
	echo 'Database host ("localhost"): ';
	if(!$host = trim(fgets(STDIN)))
		$host = 'localhost';
	echo 'Database user ("root"): ';
	if(!$user = trim(fgets(STDIN)))
		$user = 'root';
	echo 'Database password (""): ';
	$password = trim(fgets(STDIN));
	echo 'Database name ("asgard"): ';
	if(!$name = trim(fgets(STDIN)))
		$name = 'asgard';
	echo 'Database prefix (""): ';
	$prefix = trim(fgets(STDIN));

	$config = file_get_contents(__DIR__.'/database.php.sample');

	$config = str_replace('_HOST_', $host, $config);
	$config = str_replace('_USER_', $user, $config);
	$config = str_replace('_PASSWORD_', $password, $config);
	$config = str_replace('_NAME_', $name, $config);
	$config = str_replace('_PREFIX_', $prefix, $config);

	if(!file_exists(_DIR_.'config'))
		mkdir(_DIR_.'config');
	if(file_put_contents(_DIR_.'config/database.php', $config) !== false)
		echo 'Database configuration created with success.'."\n";
	else
		echo 'Database configuration creation failed.'."\n";
}