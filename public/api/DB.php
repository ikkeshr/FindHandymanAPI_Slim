<?php
class DBController{
 
    // database credentials
    private $host = "localhost";
    private $db_name = "findhandyman";
    private $username = "root";
    private $password = "";
    private $conn;
	
	
	public function __construct() {
		$this->getConnection();
	}
 
    // get the database connection
    public function getConnection(){
        $this->conn = null;
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //$this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
    }

    public function query ( $sql, $data=null ){
        if ($this->conn) {
            try {
                $sth = $this->conn->prepare($sql);
                if ($data)
                    $sth->execute($data);
                else
                    $sth->execute();
                //return json_encode( $sth->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT );
                return $sth->fetchAll(PDO::FETCH_ASSOC);
            }
            catch (PDOException $err) {
                //echo "QUERY: ". $sql . "</br>";
                //echo "ERROR: ". $err->getMessage();
                return -2;
            }
        }
        else {
            return -2;
        }
    }

    public function exec($sql, $data=null){
        if ($this->conn) {
            try {
                $sth = $this->conn->prepare($sql);
                if ($data)
                    $sth->execute($data);
                else
                    $sth->execute();
                return $sth->rowCount(); //return the number of rows affected
            }
            catch (PDOException $err) {
                echo "QUERY: ". $sql . "</br>";
                echo "ERROR: ". $err->getMessage();
                return -2;
            }
        }
        else {
            return -2;
        }
    }

    public function insertAutoId($sql, $data=null) {
        if ($this->conn) {
            try {
                $sth = $this->conn->prepare($sql);
                if ($data)
                    $sth->execute($data);
                else
                    $sth->execute();
                return $this->conn->lastInsertId();
            }
            catch (PDOException $err) {
                //echo "QUERY: ". $sql . "</br>";
                //echo "ERROR: ". $err->getMessage();
                return -2;
            }
        }
        else {
            return -2;
        }
    }
	
	public function queryBind($sql, $data) {
		/*$sql = 'SELECT name, colour, calories
				FROM fruit
				WHERE calories < :calories AND colour = :colour';*/
		//$sth->execute(array(':calories' => 150, ':colour' => 'red'));
		
		$sth = $this->conn->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$this->bindKeys($data);
		$sth->execute($data);
		return json_encode( $sth->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT );
	}
	
	public function bindKeys(& $jsObj) {
		foreach ($jsObj as $key => $value){
			$jsObj[':'.$key] = $value;
			unset ( $jsObj[$key] );
		}
    }
    
    public function beginTransaction() {
        $this->conn->beginTransaction();
    }

    public function commit() {
        $this->conn->commit();
    }

    public function rollBack() {
        $this->conn->rollBack();
    }

}

?>