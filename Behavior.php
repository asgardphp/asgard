<?php
namespace Coxis\Core;

interface Behavior {
	public static function load($entityDefinition, $params=null);
}