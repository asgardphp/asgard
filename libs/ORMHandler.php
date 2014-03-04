<?php
namespace Asgard\ORM\Libs;

class ORMHandler {
	private $entity;

	function __construct($entityDefinition) {
		$this->entity = $entityDefinition;
		if(!isset($entityDefinition->meta['order_by']))
			$entityDefinition->meta['order_by'] = 'id DESC';
		
		$entityDefinition->addProperty('id', array(
			'type'     => 'text', 
			'editable' => false, 
			'required' => false,
			'position' => 0,
			'defaut'   => 0,
			'orm'      => array(
				'type'              => 'int(11)',
				'auto_increment'	=> true,
				'key'	            => 'PRI',
				'nullable'	        => false,
			),
		));	
		static::loadRelations($entityDefinition);
	}

	public function isNew($entity) {
		return !(isset($entity->data['properties']['id']) && $entity->data['properties']['id']);
	}

	public function isOld($entity) {
		return !static::isNew($entity);
	}

	public function load($id) {
		$entityName = $this->entity->getClass();
		$entity = new $entityName($id);
		if($entity->isNew())
			return null;
		return $entity;
	}
	
	public function getORM() {
		$orm = new ORM($this->entity->getClass());
		$this->entity->trigger('getorm', array($orm));
		return $orm;
	}
	
	public function myORM($entity) {
		if($entity->isNew())
			return $this->getORM();
		else
			return $this->getORM()->where(array('id' => $entity->id));
	}
	
	public static function getTranslationTable($entity) {
		return $entity::getTable().'_translation';
	}

	public static function getTable($entityName) {
		if(isset($entityName::getDefinition()->meta['table']) && $entityName::getDefinition()->meta['table'])
			return \Asgard\Core\App::get('config')->get('database/prefix').$entityName::getDefinition()->meta['table'];
		else
			return \Asgard\Core\App::get('config')->get('database/prefix').$entityName::getEntityName();
	}
	
	public static function loadRelations($entityDefinition) {
		$entity_relations = $entityDefinition->relations();

		foreach($entityDefinition->relations() as $name=>$params)
			$entityDefinition->relations[$name] = new EntityRelation($entityDefinition, $name, $params);
	}
	
	public static function getI18N($entity, $lang) {
		$dal = new \Asgard\DB\DAL(\Asgard\Core\App::get('db'), static::getTranslationTable($entity));
		return $dal->where(array('id' => $entity->id))->where(array('locale'=>$lang))->first();
	}
	
	public function destroyAll() {
		$entityName = $this->entity->getClass();
		foreach($entityName::all() as $one)
			$entity->destroy();
	}
	
	public function destroyOne($id) {
		$entityName = $this->entity->getClass();
		if($entity = $entityName::load($id)) {
			$entity->destroy();
			return true;
		}
		return false;
	}
	
	public static function fetch($entity, $name, $lang=null) {
		if(!$entity::hasProperty($name))
			return;
		if($entity::property($name)->i18n) {
			if(!($res = static::getI18N($entity, $lang)))
				return;
			unset($res['id']);
			unset($res['locale']);

			static::unserializeSet($entity, $res, $lang);
				
			if(isset($entity->data['properties'][$name][$lang]))
				return $entity->data['properties'][$name][$lang];
		}
	}

	public function relation($entity, $name) {
		$rel = $entity::getDefinition()->relation($name);
		$relation_type = $rel['type'];
		$relEntity = $rel['entity'];
		
		switch($relation_type) {
			case 'hasOne':
			case 'belongsTo':
				if($entity->isNew())
					return;

				$link = $rel['link'];
				if($rel['polymorphic']) {
					$relEntity = $entity->{$rel['link_type']};
					if(!$relEntity)
						return;
				}
				return $relEntity::where(array('id' => $entity->$link))->first();
			case 'hasMany':
			case 'HMABT':
				if($entity->isNew())
					return;

				$collection = new \Asgard\ORM\Libs\CollectionORM($entity, $name);
				return $collection;
			default:	
				throw new \Exception('Relation '.$relation_type.' does not exist.');
		}
	}

	public function construct($chain, $entity, $id) {
		if(!ctype_digit($id) && !is_int($id))
			return;

		$res = $this->getORM()->where(array('id' => $id))->getDAL()->first();
		if($res) {
			static::unserializeSet($entity, $res);
			$chain->found = true;
		}
	}

	public static function unserializeSet($entity, $data, $lang=null) {
		foreach($data as $k=>$v)
			if($entity->hasProperty($k))
				$data[$k] = $entity->property($k)->unserialize($v, $entity);
			else
				unset($data[$k]);
		return $entity->_set($data, $lang);
	}

	public function destroy($entity) {
		$orms = array();
		foreach($entity->getDefinition()->relations() as $name=>$relation) {
			if(isset($relation['cascade']['delete']) && $relation['cascade']['delete']) {
				$orm = $entity->$name();
				if(!is_object($orm))
					continue;
				$orm->getDAL()->rsc();
				$orms[] = $orm;
			}
		}

		if($entity::isI18N())
			$r = static::myORM($entity)->getDAL()->delete(array($entity->getTable(), $entity->geti18nTable()));
		else
			$r = static::myORM($entity)->getDAL()->delete();

		foreach($orms as $orm)
			$orm->delete();

		return $r;
	}

	public function save($entity) {
		$vars = $entity->toArrayRaw();
		
		#apply filters before saving
		foreach($vars as $col => $var) {
			if($entity::property($col)->i18n) {
				foreach($var as $k=>$v)
					$vars[$col][$k] = $entity::property($col)->serialize($v);
			}
			else
				$vars[$col] = $entity::property($col)->serialize($var);
		}
		
		//Persist local id field
		foreach($entity::getDefinition()->relations as $relation => $params) {
			if(!isset($entity->data[$relation]))
				continue;
			$rel = $entity::getDefinition()->relations[$relation];
			$type = $rel['type'];
			if($type == 'belongsTo' || $type == 'hasOne') {
				$link = $rel['link'];
				if(is_object($entity->data[$relation]))
					$vars[$link] = $entity->data[$relation]->id;
				else
					$vars[$link] = $entity->data[$relation];
			}
		}
		
		//Persist i18n
		$values = array();
		$i18n = array();
		foreach($vars as $p => $v) {
			if($entity::property($p)->i18n)
				foreach($v as $lang=>$lang_value)
					$i18n[$lang][$p] = $lang_value;
			else
				$values[$p] = $v;
		}

		//Persist
		$orm = $this->getORM();
		//new
		if(!isset($entity->id) || !$entity->id)
			$entity->id = $orm->getDAL()->insert($values);
		//existing
		elseif(sizeof($vars) > 0) {
			if(!$orm->reset()->where(array('id'=>$entity->id))->getDAL()->update($values))
				$entity->id = $orm->getDAL()->insert($values);
		}		
		
		//Persist i18n
		foreach($i18n as $lang=>$values) {
			$dal = new \Asgard\DB\DAL(\Asgard\Core\App::get('db'), static::getTranslationTable($entity));
			if(!$dal->where(array('id'=>$entity->id, 'locale'=>$lang))->update($values))
				$dal->insert(
					array_merge(
						$values, 
						array(
							'locale'=>$lang,
							'id'=>$entity->id,
						)
					)
				);
		}
	
		//Persist relations
		foreach($entity::getDefinition()->relations as $relation => $params) {
			if(!isset($entity->data[$relation]))
				continue;
			$rel = $entity::getDefinition()->relations[$relation];
			$reverse_rel = $rel->reverse();
			$type = $rel['type'];

			if($type == 'hasOne') {
				$relation_entity = $rel['entity'];
				$link = $reverse_rel['link'];
				$relation_entity::where(array($link => $entity->id))->getDAL()->update(array($link => 0));
				$relation_entity::where(array('id' => $entity->data[$relation]))->getDAL()->update(array($link => $entity->id));
			}
			elseif($type == 'hasMany' || $type == 'HMABT')
				$entity->$relation()->sync($entity->data[$relation]);
		}
	}
}