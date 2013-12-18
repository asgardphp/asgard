<?php
namespace Coxis\Utils;

class Tools {
	public static function dateEscape($str) {
		return '\\'.implode('\\', str_split($str));
	}

	public static function is_function($f) {
	    return (is_object($f) && ($f instanceof \Closure));
	}

	public static function coxis_array_merge(&$a,$b){
	    foreach($b as $child=>$value) {
	        if(isset($a[$child])) {
	            if(is_array($a[$child]) && is_array($value))
	                static::coxis_array_merge($a[$child], $value);
	        }
	        else
	            $a[$child] = $value;
	    }
	}

	public static function getallheaders() { 
		$headers = ''; 
		foreach($_SERVER as $name => $value) {
			if(substr($name, 0, 5) == 'HTTP_')
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
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

	public static function hash($pwd) {
		return sha1(Config::get('salt').$pwd);
	}

	public static function pathGet($arr, $str_path, $default=null) {
		$path = explode('/', $str_path);
		return static::array_get($arr, $path, $default);
	}

	public static function pathSet(&$arr, $str_path, $value) {
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

	static $months = array(
		'January'	=>	'Janvier',
		'February'	=>	'Février',
		'March'	=>	'Mars',
		'April'	=>	'Avril',
		'May'	=>	'Mai',
		'June'	=>	'Juin',
		'July'	=>	'Juillet',
		'August'	=>	'Août',
		'September'	=>	'Septembre',
		'October'	=>	'Octobre',
		'November'	=>	'Novembre',
		'December'	=>	'Décembre',
	);

	static $shortMonths = array(
		'Jan'	=>	'Jan',
		'Feb'	=>	'Fév',
		'Mar'	=>	'Mar',
		'Apr'	=>	'Avr',
		'May'	=>	'Mai',
		'Jun'	=>	'Jui',
		'Jul'	=>	'Jui',
		'Aug'	=>	'Aoû',
		'Sep'	=>	'Sep',
		'Oct'	=>	'Oct',
		'Nov'	=>	'Nov',
		'Dec'	=>	'Déc',
	);

	static $days = array(
		'Monday'=>'Lundi',
		'Tuesday'=>'Mardi',
		'Wednesday'=>'Mercredi',
		'Thursday'=>'Jeudi',
		'Friday'=>'Vendredi',
		'Saturday'=>'Samedi',
		'Sunday'=>'Dimanche',
	);

	static $shortDays = array(
		'Mon'=>'Lun',
		'Tue'=>'Mar',
		'Wed'=>'Mer',
		'Thu'=>'Jeu',
		'Fri'=>'Ven',
		'Sat'=>'Sam',
		'Sun'=>'Dim',
	);

	static $departements = array(
				'01'	=>	'Ain',
				'02'	=>	'Aisne',
				'03'	=>	'Allier',
				'04'	=>	'Alpes de Hautes-Provence',
				'05'	=>	'Hautes-Alpes',
				'06'	=>	'Alpes-Maritimes',
				'07'	=>	'Ardèche',
				'08'	=>	'Ardennes',
				'09'	=>	'Ariège',
				'10'	=>	'Aube',
				'11'	=>	'Aude',
				'12'	=>	'Aveyron',
				'13'	=>	'Bouches-du-Rhône',
				'14'	=>	'Calvados',
				'15'	=>	'Cantal',
				'16'	=>	'Charente',
				'17'	=>	'Charente-Maritime',
				'18'	=>	'Cher',
				'19'	=>	'Corrèze',
				'2A'	=>	'Corse-du-Sud',
				'2B'	=>	'Haute-Corse',
				'21'	=>	'Côte-d\'Or',
				'22'	=>	'Côtes d\'Armor',
				'23'	=>	'Creuse',
				'24'	=>	'Dordogne',
				'25'	=>	'Doubs',
				'26'	=>	'Drôme',
				'27'	=>	'Eure',
				'28'	=>	'Eure-et-Loir',
				'29'	=>	'Finistère',
				'30'	=>	'Gard',
				'31'	=>	'Haute-Garonne',
				'32'	=>	'Gers',
				'33'	=>	'Gironde',
				'34'	=>	'Hérault',
				'35'	=>	'Ille-et-Vilaine',
				'36'	=>	'Indre',
				'37'	=>	'Indre-et-Loire',
				'38'	=>	'Isère',
				'39'	=>	'Jura',
				'40'	=>	'Landes',
				'41'	=>	'Loir-et-Cher',
				'42'	=>	'Loire',
				'43'	=>	'Haute-Loire',
				'44'	=>	'Loire-Atlantique',
				'45'	=>	'Loiret',
				'46'	=>	'Lot',
				'47'	=>	'Lot-et-Garonne',
				'48'	=>	'Lozère',
				'49'	=>	'Maine-et-Loire',
				'50'	=>	'Manche',
				'51'	=>	'Marne',
				'52'	=>	'Haute-Marne',
				'53'	=>	'Mayenne',
				'54'	=>	'Meurthe-et-Moselle',
				'55'	=>	'Meuse',
				'56'	=>	'Morbihan',
				'57'	=>	'Moselle',
				'58'	=>	'Nièvre',
				'59'	=>	'Nord',
				'60'	=>	'Oise',
				'61'	=>	'Orne',
				'62'	=>	'Pas-de-Calais',
				'63'	=>	'Puy-de-Dôme',
				'64'	=>	'Pyrénées-Atlantiques',
				'65'	=>	'Hautes-Pyrénées',
				'66'	=>	'Pyrénées-Orientales',
				'67'	=>	'Bas-Rhin',
				'68'	=>	'Haut-Rhin',
				'69'	=>	'Rhône',
				'70'	=>	'Haute-Saône',
				'71'	=>	'Saône-et-Loire',
				'72'	=>	'Sarthe',
				'73'	=>	'Savoie',
				'74'	=>	'Haute-Savoie',
				'75'	=>	'Paris',
				'76'	=>	'Seine-Maritime',
				'77'	=>	'Seine-et-Marne',
				'78'	=>	'Yvelines',
				'79'	=>	'Deux-Sèvres',
				'80'	=>	'Somme',
				'81'	=>	'Tarn',
				'82'	=>	'Tarn-et-Garonne',
				'83'	=>	'Var',
				'84'	=>	'Vaucluse',
				'85'	=>	'Vendée',
				'86'	=>	'Vienne',
				'87'	=>	'Haute-Vienne',
				'88'	=>	'Vosges',
				'89'	=>	'Yonne',
				'90'	=>	'Territoire-de-Belfort',
				'91'	=>	'Essonne',
				'92'	=>	'Hauts-de-Seine',
				'93'	=>	'Seine-Saint-Denis',
				'94'	=>	'Val-de-Marne',
				'95'	=>	'Val-d\'Oise',
	);

	public static function EntitiesToArray($entities) {
		foreach($entities as $k=>$v)
			$entities[$k] = json_decode($v->toJSON());
		return json_encode($entities);
	}
	
	public static function prependHttp($url) {
	    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
	        $url = "http://" . $url;
	    }
	    return $url;
	}

	public static function flateArray($arr) {
		if(!is_array($arr))
			return array($arr);
		$res = array();
		foreach($arr as $k=>$v)
			if(is_array($v))
				$res = array_merge($res, static::flateArray($v));
			else
				$res[] = $v;
				
		return $res;
	}
	
	public static function zip($source, $destination) {
	    if (!extension_loaded('zip') || !file_exists(_DIR_.$source)) {
		return false;
	    }

	    $zip = new \ZipArchive();
	    if (!$zip->open($destination, \ZIPARCHIVE::CREATE)) {
		return false;
	    }

	    $source = str_replace('\\', '/', realpath($source));

	    if (is_dir($source) === true)
	    {
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

		foreach ($files as $file)
		{
		    $file = str_replace('\\', '/', realpath($file));

		    if (is_dir($file) === true)
		    {
			$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
		    }
		    else if (is_file($file) === true)
		    {
			$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
		    }
		}
	    }
	    else if (is_file($source) === true)
	    {
		$zip->addFromString(basename($source), file_get_contents($source));
	    }

	    return $zip->close();
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
		
		return implode(' ', $cutwords).(sizeof($words) > sizeof($cutwords) ? $trailing:'');
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
	
	static $countries = array(
		'AF' => 'AFGHANISTAN',
		'AX' => 'Ã…LAND ISLANDS',
		'AL' => 'ALBANIA',
		'DZ' => 'ALGERIA',
		'AS' => 'AMERICAN SAMOA',
		'AD' => 'ANDORRA',
		'AO' => 'ANGOLA',
		'AI' => 'ANGUILLA',
		'AQ' => 'ANTARCTICA',
		'AG' => 'ANTIGUA AND BARBUDA',
		'AR' => 'ARGENTINA',
		'AM' => 'ARMENIA',
		'AW' => 'ARUBA',
		'AU' => 'AUSTRALIA',
		'AT' => 'AUSTRIA',
		'AZ' => 'AZERBAIJAN',
		'BS' => 'BAHAMAS',
		'BH' => 'BAHRAIN',
		'BD' => 'BANGLADESH',
		'BB' => 'BARBADOS',
		'BY' => 'BELARUS',
		'BE' => 'BELGIUM',
		'BZ' => 'BELIZE',
		'BJ' => 'BENIN',
		'BM' => 'BERMUDA',
		'BT' => 'BHUTAN',
		'BO' => 'BOLIVIA, PLURINATIONAL STATE OF',
		'BQ' => 'BONAIRE, SINT EUSTATIUS AND SABA',
		'BA' => 'BOSNIA AND HERZEGOVINA',
		'BW' => 'BOTSWANA',
		'BV' => 'BOUVET ISLAND',
		'BR' => 'BRAZIL',
		'IO' => 'BRITISH INDIAN OCEAN TERRITORY',
		'BN' => 'BRUNEI DARUSSALAM',
		'BG' => 'BULGARIA',
		'BF' => 'BURKINA FASO',
		'BI' => 'BURUNDI',
		'KH' => 'CAMBODIA',
		'CM' => 'CAMEROON',
		'CA' => 'CANADA',
		'CV' => 'CAPE VERDE',
		'KY' => 'CAYMAN ISLANDS',
		'CF' => 'CENTRAL AFRICAN REPUBLIC',
		'TD' => 'CHAD',
		'CL' => 'CHILE',
		'CN' => 'CHINA',
		'CX' => 'CHRISTMAS ISLAND',
		'CC' => 'COCOS (KEELING) ISLANDS',
		'CO' => 'COLOMBIA',
		'KM' => 'COMOROS',
		'CG' => 'CONGO',
		'CD' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
		'CK' => 'COOK ISLANDS',
		'CR' => 'COSTA RICA',
		'CI' => 'CÃ”TE D\'IVOIRE',
		'HR' => 'CROATIA',
		'CU' => 'CUBA',
		'CW' => 'CURAÃ‡AO',
		'CY' => 'CYPRUS',
		'CZ' => 'CZECH REPUBLIC',
		'DK' => 'DENMARK',
		'DJ' => 'DJIBOUTI',
		'DM' => 'DOMINICA',
		'DO' => 'DOMINICAN REPUBLIC',
		'EC' => 'ECUADOR',
		'EG' => 'EGYPT',
		'SV' => 'EL SALVADOR',
		'GQ' => 'EQUATORIAL GUINEA',
		'ER' => 'ERITREA',
		'EE' => 'ESTONIA',
		'ET' => 'ETHIOPIA',
		'FK' => 'FALKLAND ISLANDS (MALVINAS)',
		'FO' => 'FAROE ISLANDS',
		'FJ' => 'FIJI',
		'FI' => 'FINLAND',
		'FR' => 'FRANCE',
		'GF' => 'FRENCH GUIANA',
		'PF' => 'FRENCH POLYNESIA',
		'TF' => 'FRENCH SOUTHERN TERRITORIES',
		'GA' => 'GABON',
		'GM' => 'GAMBIA',
		'GE' => 'GEORGIA',
		'DE' => 'GERMANY',
		'GH' => 'GHANA',
		'GI' => 'GIBRALTAR',
		'GR' => 'GREECE',
		'GL' => 'GREENLAND',
		'GD' => 'GRENADA',
		'GP' => 'GUADELOUPE',
		'GU' => 'GUAM',
		'GT' => 'GUATEMALA',
		'GG' => 'GUERNSEY',
		'GN' => 'GUINEA',
		'GW' => 'GUINEA-BISSAU',
		'GY' => 'GUYANA',
		'HT' => 'HAITI',
		'HM' => 'HEARD ISLAND AND MCDONALD ISLANDS',
		'VA' => 'HOLY SEE (VATICAN CITY STATE)',
		'HN' => 'HONDURAS',
		'HK' => 'HONG KONG',
		'HU' => 'HUNGARY',
		'IS' => 'ICELAND',
		'IN' => 'INDIA',
		'ID' => 'INDONESIA',
		'IR' => 'IRAN, ISLAMIC REPUBLIC OF',
		'IQ' => 'IRAQ',
		'IE' => 'IRELAND',
		'IM' => 'ISLE OF MAN',
		'IL' => 'ISRAEL',
		'IT' => 'ITALY',
		'JM' => 'JAMAICA',
		'JP' => 'JAPAN',
		'JE' => 'JERSEY',
		'JO' => 'JORDAN',
		'KZ' => 'KAZAKHSTAN',
		'KE' => 'KENYA',
		'KI' => 'KIRIBATI',
		'KP' => 'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF',
		'KR' => 'KOREA, REPUBLIC OF',
		'KW' => 'KUWAIT',
		'KG' => 'KYRGYZSTAN',
		'LA' => 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC',
		'LV' => 'LATVIA',
		'LB' => 'LEBANON',
		'LS' => 'LESOTHO',
		'LR' => 'LIBERIA',
		'LY' => 'LIBYA',
		'LI' => 'LIECHTENSTEIN',
		'LT' => 'LITHUANIA',
		'LU' => 'LUXEMBOURG',
		'MO' => 'MACAO',
		'MK' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
		'MG' => 'MADAGASCAR',
		'MW' => 'MALAWI',
		'MY' => 'MALAYSIA',
		'MV' => 'MALDIVES',
		'ML' => 'MALI',
		'MT' => 'MALTA',
		'MH' => 'MARSHALL ISLANDS',
		'MQ' => 'MARTINIQUE',
		'MR' => 'MAURITANIA',
		'MU' => 'MAURITIUS',
		'YT' => 'MAYOTTE',
		'MX' => 'MEXICO',
		'FM' => 'MICRONESIA, FEDERATED STATES OF',
		'MD' => 'MOLDOVA, REPUBLIC OF',
		'MC' => 'MONACO',
		'MN' => 'MONGOLIA',
		'ME' => 'MONTENEGRO',
		'MS' => 'MONTSERRAT',
		'MA' => 'MOROCCO',
		'MZ' => 'MOZAMBIQUE',
		'MM' => 'MYANMAR',
		'NA' => 'NAMIBIA',
		'NR' => 'NAURU',
		'NP' => 'NEPAL',
		'NL' => 'NETHERLANDS',
		'NC' => 'NEW CALEDONIA',
		'NZ' => 'NEW ZEALAND',
		'NI' => 'NICARAGUA',
		'NE' => 'NIGER',
		'NG' => 'NIGERIA',
		'NU' => 'NIUE',
		'NF' => 'NORFOLK ISLAND',
		'MP' => 'NORTHERN MARIANA ISLANDS',
		'NO' => 'NORWAY',
		'OM' => 'OMAN',
		'PK' => 'PAKISTAN',
		'PW' => 'PALAU',
		'PS' => 'PALESTINIAN TERRITORY, OCCUPIED',
		'PA' => 'PANAMA',
		'PG' => 'PAPUA NEW GUINEA',
		'PY' => 'PARAGUAY',
		'PE' => 'PERU',
		'PH' => 'PHILIPPINES',
		'PN' => 'PITCAIRN',
		'PL' => 'POLAND',
		'PT' => 'PORTUGAL',
		'PR' => 'PUERTO RICO',
		'QA' => 'QATAR',
		'RE' => 'RÃ‰UNION',
		'RO' => 'ROMANIA',
		'RU' => 'RUSSIAN FEDERATION',
		'RW' => 'RWANDA',
		'BL' => 'SAINT BARTHÃ‰LEMY',
		'SH' => 'SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA',
		'KN' => 'SAINT KITTS AND NEVIS',
		'LC' => 'SAINT LUCIA',
		'MF' => 'SAINT MARTIN (FRENCH PART)',
		'PM' => 'SAINT PIERRE AND MIQUELON',
		'VC' => 'SAINT VINCENT AND THE GRENADINES',
		'WS' => 'SAMOA',
		'SM' => 'SAN MARINO',
		'ST' => 'SAO TOME AND PRINCIPE',
		'SA' => 'SAUDI ARABIA',
		'SN' => 'SENEGAL',
		'RS' => 'SERBIA',
		'SC' => 'SEYCHELLES',
		'SL' => 'SIERRA LEONE',
		'SG' => 'SINGAPORE',
		'SX' => 'SINT MAARTEN (DUTCH PART)',
		'SK' => 'SLOVAKIA',
		'SI' => 'SLOVENIA',
		'SB' => 'SOLOMON ISLANDS',
		'SO' => 'SOMALIA',
		'ZA' => 'SOUTH AFRICA',
		'GS' => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
		'SS' => 'SOUTH SUDAN',
		'ES' => 'SPAIN',
		'LK' => 'SRI LANKA',
		'SD' => 'SUDAN',
		'SR' => 'SURINAME',
		'SJ' => 'SVALBARD AND JAN MAYEN',
		'SZ' => 'SWAZILAND',
		'SE' => 'SWEDEN',
		'CH' => 'SWITZERLAND',
		'SY' => 'SYRIAN ARAB REPUBLIC',
		'TW' => 'TAIWAN, PROVINCE OF CHINA',
		'TJ' => 'TAJIKISTAN',
		'TZ' => 'TANZANIA, UNITED REPUBLIC OF',
		'TH' => 'THAILAND',
		'TL' => 'TIMOR-LESTE',
		'TG' => 'TOGO',
		'TK' => 'TOKELAU',
		'TO' => 'TONGA',
		'TT' => 'TRINIDAD AND TOBAGO',
		'TN' => 'TUNISIA',
		'TR' => 'TURKEY',
		'TM' => 'TURKMENISTAN',
		'TC' => 'TURKS AND CAICOS ISLANDS',
		'TV' => 'TUVALU',
		'UG' => 'UGANDA',
		'UA' => 'UKRAINE',
		'AE' => 'UNITED ARAB EMIRATES',
		'GB' => 'UNITED KINGDOM',
		'US' => 'UNITED STATES',
		'UM' => 'UNITED STATES MINOR OUTLYING ISLANDS',
		'UY' => 'URUGUAY',
		'UZ' => 'UZBEKISTAN',
		'VU' => 'VANUATU',
		'VE' => 'VENEZUELA, BOLIVARIAN REPUBLIC OF',
		'VN' => 'VIET NAM',
		'VG' => 'VIRGIN ISLANDS, BRITISH',
		'VI' => 'VIRGIN ISLANDS, U.S.',
		'WF' => 'WALLIS AND FUTUNA',
		'EH' => 'WESTERN SAHARA',
		'YE' => 'YEMEN',
		'ZM' => 'ZAMBIA',
		'ZW' => 'ZIMBABWE'
	);

	public static function getRelativePath($from, $to) {
		$from     = explode('/', $from);
		$to       = explode('/', $to);
		$relPath  = $to;

		foreach($from as $depth => $dir) {
			if($dir === $to[$depth])
				array_shift($relPath);
			else {
				$remaining = count($from) - $depth;
				if($remaining > 1) {
					$padLength = (count($relPath) + $remaining - 1) * -1;
					$relPath = array_pad($relPath, $padLength, '..');
					break;
				}
				else
					$relPath[0] = './' . $relPath[0];
			}
		}
		return implode('/', $relPath);
	}
}