<?php
namespace Asgard\Data;

class Data {
	protected $types=array();

	public function get($key) {
		$dal = new \Asgard\Db\Dal(\Asgard\Core\App::get('db'));
		$row = $dal->from('data')->where('key', $key)->first();
		if(isset($row['value'])) {
			$res = unserialize($row['value']);
			if(isset($res['_dataType']) && isset($res['input'])) {
				return $this->types[$res['_dataType']][1]($res['input']);
			}
			else
				return $res;
		}
	}

	public function set($key, $value, $type=null) {
		if($type === null)
			$res = serialize($value);
		else {
			$res = serialize(array(
				'_dataType' => $type,
				'input' => $this->types[$type][0]($value)
			));
		}
		$dal = new \Asgard\Db\Dal(\Asgard\Core\App::get('db'));
		if(!$dal->from('data')->where('key', $key)->update(array('value'=>$res)))
			$dal->into('data')->insert(array('key'=>$key, 'value'=>$res));
	}

	public function register($type, $serializeCb, $unserializeCb) {
		$this->types[$type] = array($serializeCb, $unserializeCb);
	}
}