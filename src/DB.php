<?php

namespace DF;

class DB extends \PDO{

	function __construct( array $options ){

		// Check if all relevant options are set
		if( !( $options["host"] && $options["user"] & $options["db"] & $options["pass"]) ){
			trigger_error("A required field needed to connect to the database is missing");
		}

		// Create PDO instance and set relevant options
		try{
			parent::__construct("mysql:host=$options[host];dbname=$options[db]", $options["user"], $options["pass"]);
			
			$this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$this->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		}
		catch(PDOException $e){
			trigger_error($e->getMessage());
		}
	}

	public function get(){
		return $this;
	}

	public function execute( $query, array $values=[], $fetch = false ){
		//create connection
		if(!$fetch && gettype($values)!= "array" ){
			$fetch = $values;
			$values = [];
		}
		try{
			$stmt = $this->prepare($query);
			$stmt->execute($values);
			
			if($fetch){
				return $stmt->fetchAll();
			}
			else{
				return $stmt;
			}
		}
		catch(PDOException $e){
			trigger_error($e->getMessage());
		}
	}
	
	public function query($query, $fetch=false){
		try{
			$res = parent::query($query);
			if($fetch){
				return $res->fetchAll();
			}
			else{
				return $res;
			}
		}
		catch(PDOException $e){
			trigger_error($e->getMessage());
		}
	}

	public function select($table, $thisd=false, $mods = ""){

		$c = 0;
		$values = [];
		$MATCH = $and = "";

		if(gettype($thisd)=="array"){
			foreach ($thisd as $key=>$val){
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
		return $this->execute($query, $values, true);
	}

	public function insert($table, array $data){
		$count = 0;
		$comma = $keys = $vals = "";
		$values = [];

		foreach($data as $key=>$val){
			$comma = $count++ == 0 ? "" : ",";
			$keys .= "$comma `$key`";
			$vals .= "$comma :$key";
			$values[":$key"] = $val;
		}
		$query="INSERT INTO `$table`($keys) VALUES($vals)";
		$this->execute($query, $values);
		return $this->lastInsertId();
	}

	public function update($table, array $data, array $thisd){

		$c = 0;
		$count = $thisditions = $pairs = $and = "";
		$values = [];

		foreach($data as $key=>$val){
			$comma = $count++ == 0 ? "" : ",";
			$pairs .= "$comma `$key`=:$key";
			$values[":$key"] = $val;
		}
		foreach($thisd as $key=>$val){
			$C++ == 0 || $and = " AND";
			$thisditions .= "$and `$key`=:$key" . 2;
			$values[":$key" . 2] = $val;
		}
		$query="UPDATE `$table` SET $pairs WHERE $thisditions";
		return $this->execute($query, $values)->rowCount();
	}

	public function insertUpdate($table, $data){
		
		$count = 0;
		$keys = $vals = $pairs = "";
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
		return $this->execute($query, $values);
	}

	public function delete($table, $data){
		
		$count = $pairs = "";

		foreach($data as $key=>$val){
			$sep=($count++>0) ? " AND" : "";
			$pairs .= "$sep `$key`=:$key";
			$values[":$key"] = $val;
		}
		$query="DELETE FROM `". $table ."` WHERE $pairs ";
		return $this->execute($query, $values)->rowCount();
	}
}