<?php
namespace Coxis\Core;

class Coxis {
	public static $facades = array(); #see end of file

	public static function setDefaultEnvironment() {
		if(!defined('_ENV_'))
			if(PHP_SAPI == 'cli' || \Server::get('HTTP_HOST') == '127.0.0.1' || \Server::get('HTTP_HOST') == 'localhost')
				define('_ENV_', 'dev');
			else
				define('_ENV_', 'prod');
	}

	public static function getExceptionResponse($e) {
		if($e instanceof \ErrorException) {
			$msg = '('.$e->getCode().') '.$e->getMessage().'<br>'.$e->getFile().' ('.$e->getLine().')';
			return \Error::report($msg, $e->getTrace());
		}
		else {
			$first_trace = array(array(
				'file'	=>	$e->getFile(),
				'line'	=>	$e->getLine(),
			));
			return \Error::report($e->getMessage(), array_merge($first_trace, $e->getTrace()));
		}
	}

	public static function load() {
		static::setDefaultEnvironment();
		BundlesManager::loadBundles();
	}
}

Coxis::$facades = array(
	'Router'			=>	'\Coxis\Core\Router',
	'Config'			=>	'\Coxis\Core\Config',
	'Hook'				=>	'\Coxis\Hook\Hook',
	'Response'			=>	'\Coxis\Core\Response',
	'Memory'			=>	'\Coxis\Core\Memory',
	'Flash'				=>	'\Coxis\Utils\Flash',
	'DB'				=>	function() {
		return new \Coxis\DB\DB(\Config::get('database'));
	},
	'CLIRouter'			=>	'\Coxis\CLI\Router',
	'Validation'		=>	'\Coxis\Validation\Validation',
	'ModelsManager'		=>	'\Coxis\Core\ModelsManager',

	'Locale'			=>	'\Coxis\Utils\Locale',

	'HTML'				=>	'\Coxis\Utils\HTML',
	'Importer'			=>	'\Coxis\Core\Importer',

	'Request'		=>	function() {
		return \Coxis\Core\Request::createFromGlobals();
	},

	'URL'				=>	function() {
		return \Request::inst()->url;
	},
	'Session'			=>	function() {
		return \Request::inst()->session;
	},
	'Get'			=>	function() {
		return \Request::inst()->get;
	},
	'Post'			=>	function() {
		return \Request::inst()->post;
	},
	'File'			=>	function() {
		return \Request::inst()->file;
	},
	'Cookie'			=>	function() {
		return \Request::inst()->cookie;
	},
	'Server'			=>	function() {
		return \Request::inst()->server;
	},
	'argv'			=>	function() {
		return \Request::inst()->argv;
	},
);