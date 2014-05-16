<?php
namespace Asgard\Auth;

interface IAuth {
	public static function isConnected();
	public static function isGuest();
	public static function check(); #throw NotAuthenticated()
	public static function attempt($user, $password);
	public static function attemptRemember();
	public static function remember($user, $password);
	public static function connect($id);
	public static function disconnect();
	public static function user();
}