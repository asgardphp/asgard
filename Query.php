<?php
namespace Asgard\DB;

class Query {
	protected $db;
	protected $rsc;

	public function __construct($db, $sql, $args=array()) {
		$this->db = $db;
		try {
			if($args) {
				$this->rsc = $db->prepare($sql);
				$this->rsc->execute($args);
			}
			else
				$this->rsc = $db->query($sql);
		} catch(\PDOException $e) {
			throw new DBException($e->getMessage().'<br/>'."\n".'SQL: '.$sql.' ('.implode($args, ', ').')');
		}
	}
	
	public function next() {
		return $this->rsc->fetch(\PDO::FETCH_ASSOC);
	}

	public function affected() {
		return $this->rsc->rowCount();
	}

	public function count() {
		return $this->rsc->rowCount();
	}

	public function first() {
		return $this->rsc->fetch(\PDO::FETCH_ASSOC);
	}

	public function all() {
		return $this->rsc->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function id() {
		return $this->db->lastInsertId();
	}
}