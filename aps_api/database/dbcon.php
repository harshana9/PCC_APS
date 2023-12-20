<?php

class DbCon {
	
	private $hostname;
	private $username;
	private $password;
	private $database;
	private $conn;

	public function __construct($iniPath="../../conf/conf.ini")
	{
		//$this->iniPath = "../../conf/conf.ini";
		$ini = parse_ini_file($iniPath);

		$this->hostname = $ini["hostname"];
		$this->username = $ini["username"];
		$this->password = $ini["password"];
		$this->database = $ini["database"];
	
		try{
			$this->conn = new PDO("mysql:host=$this->hostname;dbname=$this->database", $this->username, $this->password);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->conn->exec ("SET NAMES utf8");
		}
		catch(Exception $e)
		{
			header("Internal Server Error server", true, 500);
			exit;
		}
	}

	public function getConn(){
		return $this->conn;
	}
}
?>