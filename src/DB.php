<?php

namespace DF;

class DB{
	private $con = [];
	
	private $db = [];

	function __construct( array $db ){
		if( !( $db["host"] && $db["user"] & $db["db"] & $db["pass"]) ){
			trigger_error("A required field needed to connect to the database is missing");
		}
		$this->db = $db;
	}
	
	private function connect(){
		if( !$this->con ){
			extract ($this->db);
			$this->con = new \PDO("mysql:host=$host;dbname=$db", $user, $pass);
			
			$this->con->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$this->con->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		}
		return $this->con;
	}

	public function get(){
		return $this->connect();
	}

	static function fetch( $res ){
		return $res->fetchAll();
	}

	public function prepare( $query ){
		return $this->connect()->prepare($query);
	}

	public function execute($query, array $values=[], $fetch = false){
		//create connection
		if(!$fetch && gettype($values)!= "array" ){
			$fetch = $values;
			$values = [];
		}
		try{
			$con = $this->connect();
			$stmt = $con->prepare($query);
			$stmt->execute($values);
			
			if($fetch)
				return static::fetch($stmt);
			else
				return $stmt;
		}
		catch(PDOException $e){
			trigger_error($e->getMessage());
		}
	}
	
	public function query($query, $fetch=false){
		try{
			$con = $this->connect();
			$res = $con->query($query);
			if($fetch)
				return static::fetch($res);
			else
				return $res;
		}
		catch(PDOException $e){
			trigger_error($e->getMessage());
		}
	}

	public function select($table, $cond=false, $mods = ""){

		$values = [];
		$MATCH = "";

		if(gettype($cond)=="array"){
			foreach ($cond as $key=>$val){
				$c++ == 0 || $and = " AND";
				$MATCH .= "$and `$key` = :$key";
				$values[":$key"] = $val;
			}
			$MATCH = " WHERE $MATCH";
		}

		if(gettype($mods)=="integer"){
			$mods = "LIMIT $mods";
		}
			
		$query="SELECT * FROM `" . $table . "`" . $MATCH . " $mods";
		return static::execute($query, $values, true);
	}

	public function insert($table, array $data){
		
		$comma = $keys = $vals = "";
		$values = [];

		foreach($data as $key=>$val){
			$comma = $count++ == 0 ? "" : ",";
			$keys .= "$comma `$key`";
			$vals .= "$comma :$key";
			$values[":$key"] = $val;
		}
		$query="INSERT INTO `$table`($keys) VALUES($vals)";
		static::execute($query, $values);
		return $this->con->lastInsertId();
	}

	public function update($table, array $data, array $cond){

		$count = $conditions = $pairs = "";
		$values = [];

		foreach($data as $key=>$val){
			$comma = $count++ == 0 ? "" : ",";
			$pairs .= "$comma `$key`=:$key";
			$values[":$key"] = $val;
		}
		foreach($cond as $key=>$val){
			$C++ == 0 || $and = " AND";
			$conditions .= "$and `$key`=:$key" . 2;
			$values[":$key" . 2] = $val;
		}
		$query="UPDATE `$table` SET $pairs WHERE $conditions";
		return static::execute($query, $values)->rowCount();
	}

	public function insertUpdate($table, $data){
		
		$count = 0;
		$keys = $vals = $pairs;
		$values = [];

		foreach($data as $key=>$val){
			$comma = $count++ == 0 ? "" : ",";
			$keys .= "$comma `$key`";
			$vals .= "$comma :$key";
			$pairs .= "$comma `$key`=:$key";
			$values[":$key"] = $val;
		}
		$query="INSERT INTO `". $table ."`($keys) VALUES($vals)
		ON DUPLICATE KEY UPDATE $pairs ";
		return static::execute($query, $values);
	}

	public function delete($table, $data){
		
		$count = $pairs = "";

		foreach($data as $key=>$val){
			$sep=($count++>0) ? " AND" : "";
			$pairs .= "$sep `$key`=:$key";
			$values[":$key"] = $val;
		}
		$query="DELETE FROM `". $table ."` WHERE $pairs ";
		return static::execute($query, $values)->rowCount();
	}
}