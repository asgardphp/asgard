<?php
namespace Asgard\Entity;

class Serializer {
	protected static $singleton;

	public static function singleton() {
		if(!static::$singleton)
			static::$singleton = new static;
		return static::$singleton;
	}

	/**
	 * Convert entity to a raw array.
	 * @param  Entity  $entity
	 * @param  integer $depth
	 * @return array
	 */
	public function toArrayRaw(Entity $entity, $depth=0) {
		$res = [];

		foreach($entity->getDefinition()->properties() as $name=>$property) {
			if($entity->getDefinition()->property($name) instanceof Properties\EntityProperty) {
				if($depth < 1)
					$res[$name] = $entity->get($name, null, false);
				else {
					if($entity->getDefinition()->property($name)->get('many')) {
						foreach($entity->get($name, null, false) as $subentity)
							$res[$name][] = $subentity->toArrayRaw($depth-1);
					}
					else {
						if($subentity == $entity->get($name, null, false))
							$res[$name] = $subentity->toArrayRaw($depth-1);
						else
							$res[$name] = null;
					}
				}
			}
			elseif($entity->getDefinition()->property($name)->get('many'))
				$res[$name] = $entity->get($name)->all();
			else
				$res[$name] = $entity->get($name);
		}

		return $res;
	}

	/**
	 * Convert entity to a formatted array.
	 * @param  Entity  $entity
	 * @param  integer $depth
	 * @return array
	 */
	public function toArray(Entity $entity, $depth=0) {
		$res = [];

		foreach($entity->getDefinition()->properties() as $name=>$property) {
			if($entity->getDefinition()->property($name) instanceof Properties\EntityProperty) {
				if($depth < 1)
					continue;
				if($entity->getDefinition()->property($name)->get('many')) {
					foreach($entity->get($name, null, false) as $subentity)
						$res[$name][] = $subentity->toArray($depth-1);
				}
				else {
					if($subentity == $entity->get($name, null, false))
						$res[$name] = $subentity->toArray($depth-1);
					else
						$res[$name] = null;
				}
			}
			elseif($property->get('many')) {
				foreach($entity->get($name) as $k=>$v)
					$res[$name][$k] = $this->propertyToArray($v, $property);
			}
			else
				$res[$name] = $this->propertyToArray($entity->get($name), $property);
		}

		return $res;
	}

	/**
	 * Convert entity to json.
	 * @param  Entity  $entity
	 * @param  integer $depth
	 * @return string
	 */
	public function toJSON(Entity $entity, $depth=0) {
		return json_encode($entity->toArray($depth));
	}

	/**
	 * Convert a property to a strig or an array.
	 * @param  mixed    $v
	 * @param  Property $property
	 * @return string|array
	 */
	private function propertyToArray($v, $property) {
		if(is_null($v))
			return null;
		if(is_string($v) || is_array($v))
			return $v;
		if(method_exists($property, 'toArray'))
			return $property->toArray($v);
		elseif(method_exists($property, 'toString'))
			return $property->toString($v);
		elseif(is_object($v)) {
			if(method_exists($v, 'toArray'))
				return $v->toArray();
			elseif(method_exists($v, '__toString'))
				return $v->__toString();
		}
		throw new \Exception('Cannot convert property '.get_class($property).' to array or string.');
	}

	/**
	 * Convert entity to a raw array with translations.
	 * @param  Entity  $entity
	 * @param  array $locales
	 * @param  integer $depth
	 * @return array
	 */
	public function toArrayRawI18N(Entity $entity, array $locales=[], $depth=0) {
		if(!$locales)
			$locales = $entity->getLocales();
		$res = [];

		foreach($entity->getDefinition()->properties() as $name=>$property) {
			if($property->get('i18n')) {
				foreach($locales as $locale) {
					if($entity->getDefinition()->property($name)->get('many'))
						$res[$name][$locale] = $entity->get($name, $locale)->all();
					else
						$res[$name][$locale] = $entity->get($name, $locale);
				}
			}
			elseif($entity->getDefinition()->property($name)->get('type') == 'entity') {
				if($depth < 1)
					continue;
				if($entity->getDefinition()->property($name)->get('many')) {
					foreach($entity->get($name) as $entity)
						$res[$name][] = $entity->toArrayRawI18N($locales, $depth-1);
				}
				else
					$res[$name] = $entity->toArrayRawI18N($locales, $depth-1);
			}
			elseif($entity->getDefinition()->property($name)->get('many'))
				$res[$name] = $entity->get($name)->all();
			else
				$res[$name] = $entity->get($name);
		}

		return $res;
	}

	/**
	 * Convert entity to a formatted array with translations.
	 * @param  Entity  $entity
	 * @param  array $locales
	 * @param  integer $depth
	 * @return array
	 */
	public function toArrayI18N(Entity $entity, array $locales=[], $depth=0) {
		if(!$locales)
			$locales = $entity->getLocales();
		$res = [];

		foreach($entity->getDefinition()->properties() as $name=>$property) {
			if($property->get('i18n')) {
				foreach($locales as $locale) {
					if($entity->getDefinition()->property($name)->get('many')) {
						foreach($entity->get($name, $locale)->all() as $k=>$v)
							$res[$name][$locale][$k] = $this->propertyToArray($v, $property);
					}
					else
						$res[$name][$locale] = $this->propertyToArray($entity->get($name, $locale), $property);
				}
			}
			elseif($entity->getDefinition()->property($name)->get('type') == 'entity') {
				if($depth < 1)
					continue;
				if($entity->getDefinition()->property($name)->get('many')) {
					foreach($entity->get($name) as $entity)
						$res[$name][] = $entity->toArrayI18N($locales, $depth-1);
				}
				else
					$res[$name] = $entity->toArrayI18N($depth-1);
			}
			elseif($entity->getDefinition()->property($name)->get('many')) {
				$res[$name] = [];
				foreach($entity->get($name)->all() as $k=>$v)
					$res[$name][$k] = $this->propertyToArray($v, $property);
			}
			else
				$res[$name] = $this->propertyToArray($entity->get($name), $property);
		}

		return $res;
	}

	/**
	 * Convert entity to JSON with translations.
	 * @param  Entity  $entity
	 * @param  array $locales
	 * @param  integer $depth
	 * @return string
	 */
	public function toJSONI18N(Entity $entity, array $locales=[], $depth=0) {
		if(!$locales)
			$locales = $entity->getLocales();
		return json_encode($entity->toArrayI18N($locales, $depth));
	}

	/**
	 * Convert many entities to JSON with translations.
	 * @param  array  $entities
	 * @param  array $locales
	 * @param  integer $depth
	 * @return string
	 */
	public static function arrayToJSONI18N(array $entities, array $locales=[], $depth=0) {
		foreach($entities as $k=>$entity)
			$entities[$k] = $entity->toArrayI18N($locales, $depth);
		return json_encode($entities);
	}

	/**
	 * Convert an array of entities to json.
	 * @param  array  $entities
	 * @param  integer $depth
	 * @return string
	 */
	public static function arrayToJSON(array $entities, $depth=0) {
		foreach($entities as $k=>$entity)
			$entities[$k] = $entity->toArray($depth);
		return json_encode($entities);
	}

	/**
	 * Convert many entities to JSON with translations. Static.
	 * @param  array  $entities
	 * @param  array $locales
	 * @param  integer $depth
	 * @return string
	 */
	public static function sArrayToJSONI18N(array $entities, array $locales=[], $depth=0) {
		return static::singleton()->arrayToJSONI18N($entities, $locales, $depth);
	}

	/**
	 * Convert an array of entities to json. Static.
	 * @param  array  $entities
	 * @param  integer $depth
	 * @return string
	 */
	public static function sArrayToJSON(array $entities, $depth=0) {
		return static::singleton()->arrayToJSON($entities, $depth);
	}
}