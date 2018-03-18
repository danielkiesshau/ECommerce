<?php 

namespace Hcode\DB;

class Sql {

	const HOSTNAME = "den1.mysql1.gear.host";
	const USERNAME = "dbecommerce1";
	const PASSWORD = "Qn0o!gYO-7ZJ";
	const DBNAME = "dbecommerce1";
    const PORT = 3306;

	private $conn;

	public function __construct()
	{
		$this->conn = new \PDO(
			"mysql:dbname=".Sql::DBNAME.";host=".Sql::HOSTNAME.";charset=utf8", 
			Sql::USERNAME,
			Sql::PASSWORD,
            array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
		);

	}
    
    public static function setDBConfig($dbopts){
        Sql::HOSTNAME = $dbopts["host"];
        Sql::USERNAME = $dbopts["user"];
        Sql::PASSWORD = $dbopts["pass"];
        Sql::DBNAME = "db_ecommerce";
        Sql::PORT = $dbopts["port"];
    }

	private function setParams($statement, $parameters = array())
	{

		foreach ($parameters as $key => $value) {
			
			$this->bindParam($statement, $key, $value);

		}

	}

	private function bindParam($statement, $key, $value)
	{

		$statement->bindParam($key, $value);

	}

	public function query($rawQuery, $params = array())
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

	}

	public function select($rawQuery, $params = array()):array
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_ASSOC);

	}

}

 ?>