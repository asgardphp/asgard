<?php
namespace Asgard\Common;

/**
 * Random util functions.
 * @api
 */
class Tools {
	/**
	 * Truncate HTML code.
	 * @param  string  $html
	 * @param  integer $maxLength
	 * @param  string  $trailing
	 * @return string
	 * @api
	 */
	public static function truncateHTML($html, $maxLength, $trailing='...') {
		$html = trim($html);
		$printedLength = 0;
		$position = 0;
		$tags = [];

		$res = '';

		while ($printedLength < $maxLength && preg_match('{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}', $html, $match, PREG_OFFSET_CAPTURE, $position)) {
			list($tag, $tagPosition) = $match[0];

			$str = substr($html, $position, $tagPosition - $position);
			if($printedLength + strlen($str) > $maxLength) {
				$res .= (substr($str, 0, $maxLength - $printedLength));
				$printedLength = $maxLength;
				break;
			}

			$res .= ($str);
			$printedLength += strlen($str);

			if($tag[0] == '&') {
				$res .= ($tag);
				$printedLength++;
			}
			else {
				$tagName = $match[1][0];
				if($tag[1] == '/')
					$res .= ($tag);
				elseif($tag[strlen($tag) - 2] == '/' || $tagName == 'br' || $tagName == 'hr')
					$res .= ($tag);
				else {
					$res .= ($tag);
					$tags[] = $tagName;
				}
			}

			$position = $tagPosition + strlen($tag);
		}

		if($printedLength < $maxLength && $position < strlen($html))
			$res .= (substr($html, $position, $maxLength - $printedLength));

		if($position < strlen($html))
			$res .= $trailing;

		while(!empty($tags))
			$res .= sprintf('</%s>', array_pop($tags));

		return $res;
	}

	/**
	 * Truncate a string.
	 * @param  string  $str
	 * @param  integer $length
	 * @param  string  $trailing
	 * @return string
	 * @api
	 */
	public static function truncate($str, $length, $trailing='...') {
		$length -= mb_strlen($trailing);

		if (mb_strlen($str) > $length)
			return mb_substr($str,0,$length).$trailing;
		return $str;
	}

	/**
	 * Truncate a string by words.
	 * @param  string  $str
	 * @param  integer $length
	 * @param  string $trailing
	 * @return string
	 * @api
	 */
	public static function truncateWords($str, $length, $trailing='...') {
		$words = explode(' ', $str);
		$cutwords = array_slice($words, 0, $length);

		return implode(' ', $cutwords).(count($words) > count($cutwords) ? $trailing:'');
	}

	/**
	 * Remove accents.
	 * @param  string $str
	 * @param  string $charset
	 * @return string
	 * @api
	 */
	public static function removeAccents($str, $charset='utf-8') {
		$str = htmlentities($str, ENT_NOQUOTES, $charset);

		$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
		$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
		$str = preg_replace('#&[^;]+;#', '', $str);

		return $str;
	}

	/**
	 * Generate a random string.
	 * @param  integer $length
	 * @param  string  $validCharacters
	 * @return string
	 * @api
	 */
	public static function randstr($length=10, $validCharacters = 'abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ0123456789') {
		$validCharNumber = strlen($validCharacters);
		$result = '';

		for ($i=0; $i < $length; $i++) {
			$index = mt_rand(0, $validCharNumber - 1);
			$result .= $validCharacters[$index];
		}

		return $result;
	}

	/**
	 * Load a file and return the class contained in the file.
	 * @param  string $file
	 * @return string
	 * @api
	 */
	public static function loadClassFile($file) {
		$before = array_merge(get_declared_classes(), get_declared_interfaces());
		require_once $file;
		$after = array_merge(get_declared_classes(), get_declared_interfaces());

		$diff = array_diff($after, $before);
		$result = array_values($diff)[count($diff)-1];
		if(!$result) {
			foreach(array_merge(get_declared_classes(), get_declared_interfaces()) as $class) {
				$reflector = new \ReflectionClass($class);
				if($reflector->getFileName() == realpath($file)) {
					$result = $class;
					break;
				}
			}
		}

		return $result;
	}
}