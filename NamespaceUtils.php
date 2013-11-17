<?php
namespace Coxis\Utils;

class NamespaceUtils {
	public static function basename($ns) {
		return basename(str_replace('\\', DIRECTORY_SEPARATOR, $ns));
	}

	public static function dirname($ns) {
		return str_replace(DIRECTORY_SEPARATOR, '\\', dirname(str_replace('\\', DIRECTORY_SEPARATOR, $ns)));
	}
}