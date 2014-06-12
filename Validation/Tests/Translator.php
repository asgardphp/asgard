<?php
namespace Asgard\Validation\Tests;
use Symfony\Component\Translation\TranslatorInterface;

class Translator implements TranslatorInterface {
	protected $catalogue = [
		':attribute must be equal to :equal.' => ':attribute doit être égal à :equal.'
	];

	public function trans($id, array $parameters = [], $domain = null, $locale = null) {
		return $this->catalogue[$id];
	}

	public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null) {}
	public function setLocale($locale) {}
	public function getLocale() {}
}