<?php
namespace Asgard\Utils;

class Tools {
	public static function is_function($f) {
	    return (is_object($f) && ($f instanceof \Closure));
	}

	public static function getallheaders() {
		$headers = array();
		foreach($_SERVER as $name => $value) {
			if(substr($name, 0, 5) == 'HTTP_')
				$headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
		}
		return $headers; 
	}

	public static function var_dump_to_string($var){
		ob_start();
		var_dump($var);
		$str = ob_get_contents();
		ob_end_clean();
		return $str;
	}

	public static function string_array_get($arr, $str_path, $default=null) {
		$path = explode('/', $str_path);
		return static::array_get($arr, $path, $default);
	}

	public static function string_array_set(&$arr, $str_path, $value) {
		$path = explode('/', $str_path);
		static::array_set($arr, $path, $value);
	}

	public static function array_set(&$arr, $path, $value) {
		if(!is_array($path))
			$path = array($path);
		$lastkey = array_pop($path);
		foreach($path as $parent)
			$arr =& $arr[$parent];
		$arr[$lastkey] = $value;
	}
	
	public static function array_get($arr, $path, $default=null) {
		if(!is_array($path))
			$path = array($path);
		foreach($path as $key) {
			if(!isset($arr[$key]))
				return $default;
			else
				$arr = $arr[$key];
		}
		return $arr;
	}
	
	public static function array_isset($arr, $keys) {
		if(!$keys)
			return;
		if(!is_array($keys))
			$keys = array($keys);
		foreach($keys as $key) {
			if(!isset($arr[$key]))
				return false;
			else
				$arr = $arr[$key];
		}
		return true;
	}
	
	public static function array_unset(&$arr, $keys) {
		if(!$keys)
			return;
		if(!is_array($keys))
			$keys = array($keys);
		$lastkey = array_pop($keys);
		foreach($keys as $parent)
			$arr =& $arr[$parent];
		unset($arr[$lastkey]);
	}

	public static function flateArray($arr) {
		if(!is_array($arr))
			return array($arr);
		$res = array();
		foreach($arr as $k=>$v) {
			if(is_array($v))
				$res = array_merge($res, static::flateArray($v));
			else
				$res[] = $v;
		}
				
		return $res;
	}
	
	public static function array_before($arr, $i) {
		$res = array();
		foreach($arr as $k=>$v) {
			if($k === $i)
				return $res;
			$res[$k] = $v;
		}
		return $res;
	}

	public static function array_after($arr, $i) {
		$res = array();
		$do = false;
		foreach($arr as $k=>$v) {
			if($do)
				$res[$k] = $v;
			if($k === $i)
				$do = true;
		}
		return $res;
	}
	
	public static function truncateHTML($html, $maxLength, $trailing='...') {
		$html = trim($html);
		$printedLength = 0;
		$position = 0;
		$tags = array();
		
		$res = '';

		while ($printedLength < $maxLength && preg_match('{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}', $html, $match, PREG_OFFSET_CAPTURE, $position)) {
			list($tag, $tagPosition) = $match[0];

			// Print text leading up to the tag.
			$str = substr($html, $position, $tagPosition - $position);
			if ($printedLength + strlen($str) > $maxLength) {
				$res .= (substr($str, 0, $maxLength - $printedLength));
				$printedLength = $maxLength;
				break;
			}

			$res .= ($str);
			$printedLength += strlen($str);

			if ($tag[0] == '&') {
				// Handle the entity.
				$res .= ($tag);
				$printedLength++;
			}
			else {
				// Handle the tag.
				$tagName = $match[1][0];
				if($tag[1] == '/') {
					// This is a closing tag.

					$openingTag = array_pop($tags);

					$res .= ($tag);
				}
				else if ($tag[strlen($tag) - 2] == '/' || $tagName == 'br' || $tagName == 'hr') {
					// Self-closing tag.
					$res .= ($tag);
				}
				else {
					// Opening tag.
					$res .= ($tag);
					$tags[] = $tagName;
				}
			}

			// Continue after the tag.
			$position = $tagPosition + strlen($tag);
		}

		// Print any remaining text.
		if ($printedLength < $maxLength && $position < strlen($html))
			$res .= (substr($html, $position, $maxLength - $printedLength));
			
		if($position < strlen($html))
			$res .= $trailing;
			
		// Close any open tags.
		while (!empty($tags))
			$res .= sprintf('</%s>', array_pop($tags));
			
		return $res;
	}

	public static function truncate($str, $length, $trailing='...') {
		// take off chars for the trailing
		$length-=mb_strlen($trailing);
		
		if (mb_strlen($str)> $length)
			// string exceeded length, truncate and add trailing dots
			return mb_substr($str,0,$length).$trailing;
		else
			// string was already short enough, return the string
			$res = $str;

		return $res;
	}
	
	public static function truncateWords($str, $length, $trailing='...') {
		$words = explode(' ', $str);
		
		$cutwords = array_slice($words, 0, 15);
		
		return implode(' ', $cutwords).(count($words) > count($cutwords) ? $trailing:'');
	}
	
	protected static function remove_accents($str, $charset='utf-8') {
		$str = htmlentities($str, ENT_NOQUOTES, $charset);
		
		$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
		$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
		$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
		
		return $str;
	}
	
	static public function slugify($text) {
		$text = static::remove_accents($text);
	
		// replace non letter or digits by -
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text);

		// trim
		$text = trim($text, '-');

		// transliterate
		if (function_exists('iconv'))
			$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// lowercase
		$text = strtolower($text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		if (empty($text))
			return 'n-a';

		return $text;
	}
	
	static public function randstr($length=10, $validCharacters = 'abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ0123456789') {
		$validCharNumber = strlen($validCharacters);

		$result = '';

		for ($i = 0; $i < $length; $i++) {
			$index = mt_rand(0, $validCharNumber - 1);
			$result .= $validCharacters[$index];
		}

		return $result;
	}

	public static function hash($pwd) {
		return sha1(\Asgard\Core\App::get('config')->get('key').$pwd);
	}

	// public static function getRelativePath($from, $to) {
	// 	$from     = explode('/', $from);
	// 	$to       = explode('/', $to);
	// 	$relPath  = $to;

	// 	foreach($from as $depth => $dir) {
	// 		if($dir === $to[$depth])
	// 			array_shift($relPath);
	// 		else {
	// 			$remaining = count($from) - $depth;
	// 			if($remaining > 1) {
	// 				$padLength = (count($relPath) + $remaining - 1) * -1;
	// 				$relPath = array_pad($relPath, $padLength, '..');
	// 				break;
	// 			}
	// 			else
	// 				$relPath[0] = './' . $relPath[0];
	// 		}
	// 	}
	// 	return implode('/', $relPath);
	// }

	// public static function asgard_array_merge(&$a,$b){
	//     foreach($b as $child=>$value) {
	//         if(isset($a[$child])) {
	//             if(is_array($a[$child]) && is_array($value))
	//                 static::asgard_array_merge($a[$child], $value);
	//         }
	//         else
	//             $a[$child] = $value;
	//     }
	// }
	
	// public static function prependHttp($url) {
	//     if (!preg_match("~^(?:f|ht)tps?://~i", $url))
	//         $url = "http://" . $url;
	//     return $url;
	// }
	
	// public static function zip($source, $destination) {
	//     if (!extension_loaded('zip') || !file_exists(_DIR_.$source))
	// 		return false;

	//     $zip = new \ZipArchive();
	//     if (!$zip->open($destination, \ZIPARCHIVE::CREATE))
	// 		return false;

	//     $source = str_replace('\\', '/', realpath($source));

	//     if (is_dir($source) === true) {
	// 		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

	// 		foreach ($files as $file) {
	// 	    	$file = str_replace('\\', '/', realpath($file));

	// 		    if (is_dir($file) === true)
	// 				$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
	// 		    else if (is_file($file) === true)
	// 				$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
	// 		}
	//     }
	//     else if (is_file($source) === true)
	// 		$zip->addFromString(basename($source), file_get_contents($source));

	//     return $zip->close();
	// }
}