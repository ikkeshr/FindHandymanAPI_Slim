<?php
class DB{
 
    // database credentials
    private $host = "localhost";
    private $db_name = "findhandyman_prod";
    private $username = "root";
    private $password = ""; //root1234 on 000webhost
    private $conn;
    private $response;
	
	
	public function __construct() {
        $this->initResponseStruct();
        // Connection to database
		$this->conn = null;
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $exception){
            //echo "Connection error: " . $exception->getMessage();
            $this->response['status']['code'] = 500;
            $this->response['status']['msg'] = 'Database connection error';
            $this->response['status']['rawMsg'] = $exception->getMessage();
        }
    }
    
    public function initResponseStruct() {
        $this->response['status']['code'] = 200;
        $this->response['status']['msg'] = '';
        $this->response['status']['rawMsg'] = '';
        $this->response['data'] = [];
    }
 
    public function query ( $sql, $data=null ){
        if ($this->conn) {
            $this->initResponseStruct();
            try {
                $sth = $this->conn->prepare($sql);
                if ($data)
                    $sth->execute($data);
                else
                    $sth->execute();
                $res = $sth->fetchAll(PDO::FETCH_ASSOC);
                $this->response['status']['code'] = 200;
                $this->response['status']['msg'] = 'QUERY DATA FETCHED';
                $this->response['data'] = $res;
            }
            catch (PDOException $exception) {
                $this->response['status']['code'] = 500;
                $this->response['status']['msg'] = 'QUERY ERROR';
                $this->response['status']['rawMsg'] = $exception->getMessage();
            }
        }
        return $this->response;
    }

    public function exec($sql, $data=null){
        if ($this->conn) {
            $this->initResponseStruct();
            try {
                $sth = $this->conn->prepare($sql);
                if ($data)
                    $sth->execute($data);
                else
                    $sth->execute();
                $rowsAffected =  $sth->rowCount(); //get the number of rows affected
                $this->response['status']['code'] = 200;
                $this->response['status']['msg'] = 'EXECUTION SUCCESFULL';
                $this->response['data']['rowsAffected'] = $rowsAffected;
            }
            catch (PDOException $exception) {
                $this->response['status']['code'] = 500;
                $this->response['status']['msg'] = 'EXECUTION ERROR';
                $this->response['status']['rawMsg'] = $exception->getMessage();
            }
        }
        return $this->response;
    }

    public function insertAutoId($sql, $data=null) {
        if ($this->conn) {
            $this->initResponseStruct();
            try {
                $sth = $this->conn->prepare($sql);
                if ($data)
                    $sth->execute($data);
                else
                    $sth->execute();
                $lastInsertId = $this->conn->lastInsertId();
                $this->response['status']['code'] = 200;
                $this->response['status']['msg'] = 'INSERTED SUCCESFULLY';
                $this->response['data']['lastInsertId'] = $lastInsertId;
            }
            catch (PDOException $exception) {
                $this->response['status']['code'] = 500;
                $this->response['status']['msg'] = 'INSERTION ERROR';
                $this->response['status']['rawMsg'] = $exception->getMessage();
            }
        }
        return $this->response;
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