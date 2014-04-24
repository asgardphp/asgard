<?php
require_once 'paths.php';

function randstr($length=10, $validCharacters = 'abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ0123456789') {
	$validCharNumber = strlen($validCharacters);

	$result = '';

	for ($i = 0; $i < $length; $i++) {
		$index = mt_rand(0, $validCharNumber - 1);
		$result .= $validCharacters[$index];
	}

	return $result;
}

if(file_exists('config/config.php'))
	echo 'File "config/config.php" already exists.'."\n";
else {
	$config = file_get_contents(__DIR__.'/config.php.sample');
	$key = randStr(10);
	$config = str_replace('_KEY_', $key, $config);

	if(!file_exists(_DIR_.'config'))
		mkdir(_DIR_.'config');
	if(file_put_contents(_DIR_.'config/config.php', $config) !== false)
		echo 'Configuration created with success.'."\n";
	else
		echo 'Configuration creation failed.'."\n";			
}