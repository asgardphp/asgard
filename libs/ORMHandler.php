<?php
namespace Coxis\ORM\Libs;

class ORMHandler {
	private $model;

	function __construct($modelDefinition) {
		$this->model = $modelDefinition;
		if(!isset($modelDefinition->meta['order_by']))
			$modelDefinition->meta['order_by'] = 'id DESC';
		
		$modelDefinition->addProperty('id', array(
			'type' => 'text', 
			'editable'=>false, 
			'required'=>false,
			'position'	=>	0,
			'defaut'	=>	0,
			'orm'	=>	array(
				'type'	=>	'int(11)',
				'auto_increment'	=>	true,
				'key'	=>	'PRI',
				'nullable'	=>	false,
			),
		));	
		static::loadRelations($modelDefinition);
	}

	public function isNew($model) {
		return !(isset($model->data['properties']['id']) && $model->data['properties']['id']);
	}

	public function isOld($model) {
		return !static::isNew($model);
	}

	public function load($id) {
		$modelName = $this->model->getClass();
		$model = new $modelName($id);
		if($model->isNew())
			return null;
		return $model;
	}
	
	public function getORM() {
		$orm = new ORM($this->model->getClass());
		$this->model->trigger('getorm', array($orm));
		return $orm;
	}
	
	public function myORM($model) {
		if($model->isNew())
			return $this->getORM();
		else
			return $this->getORM()->where(array('id' => $model->id));
	}
	
	public static function getTranslationTable($model) {
		return $model::getTable().'_translation';
	}

	public static function getTable($modelName) {
		if(isset($modelName::getDefinition()->meta['table']) && $modelName::getDefinition()->meta['table'])
			return \Config::get('database', 'prefix').$modelName::getDefinition()->meta['table'];
		else
			return \Config::get('database', 'prefix').$modelName::getModelName();
	}
	
	public static function loadRelations($modelDefinition) {
		$model_relations = $modelDefinition->relations();

		foreach($modelDefinition->relations() as $name=>$params)
			$modelDefinition->relations[$name] = new ModelRelation($modelDefinition, $name, $params);
	}
	
	public static function getI18N($model, $lang) {
		$dal = new \Coxis\DB\DAL(static::getTranslationTable($model));
		return $dal->where(array('id' => $model->id))->where(array('locale'=>$lang))->first();
	}
	
	public function destroyAll() {
		$modelName = $this->model->getClass();
		foreach($modelName::all() as $one)
			$model->destroy();
	}
	
	public function destroyOne($id) {
		$modelName = $this->model->getClass();
		if($model = $modelName::load($id)) {
			$model->destroy();
			return true;
		}
		return false;
	}
	
	public static function fetch($model, $name, $lang=null) {
		if(!$model::hasProperty($name))
			return;
		if($model::property($name)->i18n) {
			if(!($res = static::getI18N($model, $lang)))
				return;
			unset($res['id']);
			unset($res['locale']);

			static::unserializeSet($model, $res, $lang);
				
			if(isset($model->data['properties'][$name][$lang]))
				return $model->data['properties'][$name][$lang];
		}
	}

	public function relation($model, $name) {
		$rel = $model::getDefinition()->relation($name);
		$relation_type = $rel['type'];
		$relmodel = $rel['model'];
		
		switch($relation_type) {
			case 'hasOne':
				if($model->isNew())
					return;
				
				$link = $rel['link'];
				if($rel['polymorphic']) {
					$relmodel = $model->{$rel['link_type']};
					if(!$relmodel)
						return;
				}
				return $relmodel::where(array('id' => $model->{$link}))->first();
			case 'belongsTo':
				if($model->isNew())
					return;

				$link = $rel['link'];
				if($rel['polymorphic']) {
					$relmodel = $model->{$rel['link_type']};
					if(!$relmodel)
						return;
				}
				return $relmodel::where(array('id' => $model->$link))->first();
			case 'hasMany':
			case 'HMABT':
				if($model->isNew())
					return;

				$collection = new \Coxis\ORM\Libs\CollectionORM($model, $name);
				return $collection;
			default:	
				throw new \Exception('Relation '.$relation_type.' does not exist.');
		}
	}

	public function construct($chain, $model, $id) {
		if(!ctype_digit($id) && !is_int($id))
			return;

		$res = $this->getORM()->where(array('id' => $id))->getDAL()->first();
		if($res) {
			static::unserializeSet($model, $res);
			$chain->found = true;
		}
	}

	public static function unserializeSet($model, $data, $lang=null) {
		foreach($data as $k=>$v)
			if($model->hasProperty($k))
				$data[$k] = $model->property($k)->unserialize($v, $model);
			else
				unset($data[$k]);
		return $model->set($data, $lang, true);
	}

	public function destroy($model) {
		$orms = array();
		foreach($model->getDefinition()->relations() as $name=>$relation)
			if(isset($relation['cascade']['delete'])) {
				$orm = $model->$name();
				if(!is_object($orm))
					continue;
				$orm->getDAL()->rsc();
				$orms[] = $orm;
			}

		if($model::isI18N())
			$r = static::myORM($model)->getDAL()->delete(array($model->getTable(), $model->geti18nTable()));
		else
			$r = static::myORM($model)->getDAL()->delete();

		foreach($orms as $orm)
			$orm->delete();

		return $r;
	}

	public function save($model) {
		$vars = $model->toArrayRaw();
		
		#apply filters before saving
		foreach($vars as $col => $var) {
			if($model::property($col)->filter) {
				$filter = $model::property($col)->filter['to'];
				$vars[$col] = $model::$filter($var);
			}
			else {
				if($model::property($col)->i18n)
					foreach($var as $k=>$v)
						$vars[$col][$k] = $model::property($col)->serialize($v);
				else
					$vars[$col] = $model::property($col)->serialize($var);
			}
		}
		
		//Persist local id field
		foreach($model::getDefinition()->relations as $relation => $params) {
			if(!isset($model->data[$relation]))
				continue;
			$rel = $model::getDefinition()->relations[$relation];
			$type = $rel['type'];
			if($type == 'belongsTo' || $type == 'hasOne') {
				$link = $rel['link'];
				if(is_object($model->data[$relation]))
					$vars[$link] = $model->data[$relation]->id;
				else
					$vars[$link] = $model->data[$relation];
			}
		}
		
		//Persist i18n
		$values = array();
		$i18n = array();
		foreach($vars as $p => $v) {
			if($model::property($p)->i18n)
				foreach($v as $lang=>$lang_value)
					$i18n[$lang][$p] = $lang_value;
			else
				$values[$p] = $v;
		}

		//Persist
		$orm = $this->getORM();
		//new
		if(!isset($model->id) || !$model->id)
			$model->id = $orm->getDAL()->insert($values);
		//existing
		elseif(sizeof($vars) > 0) {
			if(!$orm->reset()->where(array('id'=>$model->id))->getDAL()->update($values))
				$model->id = $orm->getDAL()->insert($values);
		}		
		
		//Persist i18n
		foreach($i18n as $lang=>$values) {
			$dal = new \Coxis\DB\DAL(static::getTranslationTable($model));
			if(!$dal->where(array('id'=>$model->id, 'locale'=>$lang))->update($values))
				$dal->insert(
					array_merge(
						$values, 
						array(
							'locale'=>$lang,
							'id'=>$model->id,
						)
					)
				);
		}
	
		//Persist relations
		foreach($model::getDefinition()->relations as $relation => $params) {
			if(!isset($model->data[$relation]))
				continue;
			$rel = $model::getDefinition()->relations[$relation];
			$reverse_rel = $rel->reverse();
			$type = $rel['type'];

			if($type == 'hasOne') {
				$relation_model = $rel['model'];
				$link = $reverse_rel['link'];
				$relation_model::where(array($link => $model->id))->getDAL()->update(array($link => 0));
				$relation_model::where(array('id' => $model->data[$relation]))->getDAL()->update(array($link => $model->id));
			}
			elseif($type == 'hasMany' || $type == 'HMABT')
				$model->$relation()->sync($model->data[$relation]);
		}
	}
}