<?php
defined('ROOT') or die;
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

class Database
{	
	private $link, $result, $params = null;
	
	public function __Construct($hostname, $username, $password, $database)
	{
		if(isset($this->link))
		{
			return;
		}
		
		$this->link = $this->connect($hostname, $username, $password, $database);
	}
	
	public function query($sql)
	{	
		return $this->prepare($sql)->execute();
	}
	
	public function prepare($sql)
	{
		if(is_object($this->result))
		{
			unset($this->result);
		}
		
		$this->result = $this->link->prepare($sql);
		return $this;
	}
	
	public function bind_param()
	{
		if(is_array($this->params))
		{
			unset($this->params);
		}
		
		$this->params = func_get_args();
		return $this;
	}
	
	public function execute()
	{
		if($this->result->execute($this->params))
		{
			return new PDOResult($this->result, $this->link->lastInsertId());
		}
		
		$error = $this->result->errorInfo();
		return trigger_error($error[1].' '.$error[2], E_USER_ERROR);
	}
	
	public function insert_array($table, $array)
	{
		$values = array();
		foreach($array as $key => $value)
		{
			$values[] = "'{$value}'";
		}
		
		return $this->prepare('INSERT INTO '.$table.' ('.implode(',', array_keys($array)).') VALUES ('.implode(',', $values).')')->execute();
	}
	
	private function connect($hostname, $username, $password, $database)
	{
		return new PDO("mysql:dbname={$database};host={$hostname}", $username, $password, array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
	}	
}

class PDOResult
{
	private $result;
	public $num_rows, $insert_id;
	
	public function __Construct($result, $insert_id)
	{		
		$this->check();
		
		$this->insert_id = $insert_id;
		$this->result = $result;
		$this->num_rows = $this->result->rowCount();
	}
	
	public function result()
	{
		return $this->result->fetchColumn();
	}
	
	public function fetch_assoc()
	{
		return $this->result->fetch(PDO::FETCH_ASSOC);
	}
	
	private function check()
	{
		if(is_object($this->result)) { unset($this->result); }
	}
}
?>