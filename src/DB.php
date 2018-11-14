<?php

namespace DF;

/*
	A simple class for reading and writing data
	
	METHODS
	
	static query(string sql, [function $callback], [boolean $fetch])
		The query function is used to execute an sql statement from the database. It use a child class of MysqlO
		This function is used by all other methods to perform operations
		* $callback function can be used to execute a specific function with the result of the query;
		* $fetch = true will return an associative array after executing the query
		
	static read(string $table, array $match)
	
	static update(string $table, array $match)
		This function can be used for creating new entries and also for updating existing ones
		It uses entry update if a Duplicate key is found
		
	static delete(string $table, array $match)
		just as the name sounds
		
		
	static read
*/
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

	public function getPDO(){
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