<?php
namespace Asgard\Core;

interface Behavior {
	public static function load($entityDefinition, $params=null);
}