<?php

class DB{

    protected $conn = null;

	private $Host;
	private $DBName;
	private $DBUser;
	private $DBPassword;
	private $DBPort;

	public function __construct($Host, $DBName, $DBUser, $DBPassword, $DBPort){
		
		$this->Host       = $Host;
		$this->DBName     = $DBName;
		$this->DBUser     = $DBUser;
		$this->DBPassword = $DBPassword;
		$this->DBPort = $DBPort;
		
		//return $this->Connect();
		
	}
	
	
    public function Connect(){
        try {

            $dsn = "mysql:dbname=". $this->DBName .";port=". $this->DBPort ."; host=". $this->Host .";charset=utf8mb4";
            $user = $this->DBUser;
            $password = $this->DBPassword;

            $options  = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_PERSISTENT => false
            );

            $this->conn = new PDO($dsn, $user, $password, $options);
            return $this->conn;

        } catch (PDOException $e) {
			$this->Close();
			return false;
        }
    }

    public function Close(){
        return $this->conn = null;
    }
}