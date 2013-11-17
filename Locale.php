<?php
namespace Coxis\Utils;

class Locale {
	protected $default = 'en';
	public $locales = array();

	function __construct() {
		static::setLocale(\Config::get('locale'));
	}

	public function addLocales($locales) {
		$this->locales = array_merge_recursive($this->locales, $locales);
		return $this;
	}

	public function setLocales($locales) {
		$this->locales = $locales;
		return $this;
	}

	public function getLocales() {
		return $this->locales;
	}

	public function setDefault($locale) {
		$this->default = $locale;
	}

	public static function setLocale($locale) {
		\Config::set('locale', $locale);
	}

	public function translate($key, $params=array()) {
		$locale = \Config::get('locale');
		if(isset($this->locales[$locale][$key]) && $this->locales[$locale][$key])
			$str = $this->locales[$locale][$key];
		elseif(isset($this->locales[$this->default][$key]) && $this->locales[$this->default][$key])
			$str = $this->locales[$this->default][$key];
		else
			$str = $key;
	
		foreach($params as $k=>$v)
			$str = str_replace(':'.$k, $v, $str);
		
		return $str;
	}
	
	public function importLocales($dir) {
		if(is_array(glob($dir.'/*'))) {
			foreach(glob($dir.'/*') as $lang_dir) {
				$lang = basename($lang_dir);
				foreach(glob($lang_dir.'/*') as $file)
					$this->import($lang, $file);
			}
		}
	}
	
	public function import($lang, $file) {
		$yaml = new \Symfony\Component\Yaml\Parser();
		$raw = $yaml->parse(file_get_contents($file));
		if(!isset($this->locales[$lang]))
			$this->locales[$lang] = array();
		if(is_array($raw))
			$this->locales[$lang] = array_merge($this->locales[$lang], $raw);
	}

	public function fetchLocalesFromDir($dir) {
		$locales = array();
		if(is_array(glob($dir.'/*'))) {
			foreach(glob($dir.'/*') as $lang_dir) {
				$lang = basename($lang_dir);
				foreach(glob($lang_dir.'/*') as $file)
					$locales = array_merge($locales, $this->fetchLocalesFromFile($file));
			}
		}
		return $locales;
	}

	public function fetchLocalesFromFile($file) {
		$yaml = new \Symfony\Component\Yaml\Parser();
		$raw = $yaml->parse(file_get_contents($file));
		if(is_array($raw))
			return $raw;
		return $raw;
	}
}