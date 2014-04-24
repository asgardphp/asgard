<?php
namespace Asgard\Translation;

class Translator implements \Symfony\Component\Translation\TranslatorInterface {
	protected $fallback = 'en';
	protected $locale = 'en';
	public $locales = array();

	public function __construct() {
		static::setLocale(\Asgard\Core\App::get('config')->get('locale'));
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

	public function setFallback($locale) {
		$this->fallback = $locale;
	}

	public function setLocale($locale) {
		$this->locale = $locale;
	}

	public function getLocale() {
		return $this->locale;
	}

	public function trans($key, array $params = array(), $domain = null, $locale = null) {
		if($locale == null)
			$locale = $this->getLocale();
		if(isset($this->locales[$locale][$key]) && $this->locales[$locale][$key])
			$str = $this->locales[$locale][$key];
		elseif(isset($this->locales[$this->fallback][$key]) && $this->locales[$this->fallback][$key])
			$str = $this->locales[$this->fallback][$key];
		else
			$str = $key;
	
		foreach($params as $k=>$v)
			$str = str_replace(':'.$k, $v, $str);
		
		return $str;
	}

	public function transChoice($key, $number, array $params = array(), $domain = null, $locale = null) {
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