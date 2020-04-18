<?php
class DBController{
 
    // specify your own database credentials
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
            //$this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }

    }
	
	public function query ( $sql ){
        $sth = $this->conn->prepare($sql);
        $sth->execute();
        return json_encode( $sth->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT );
    }

    public function query2 ( $sql, $data ){
        $sth = $this->conn->prepare($sql);
        $sth->execute($data);
        return json_encode( $sth->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT );
    }

    public function exec($sql){
        $sth = $this->conn->prepare($sql);
        return $sth->execute();
    }

    public function exec2($sql, $data){
        $sth = $this->conn->prepare($sql);
        return $sth->execute($data);
    }

    public function insertAutoId($sql, $data) {
        $sth = $this->conn->prepare($sql);
        $sth->execute($data);
        return $this->conn->lastInsertId();
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

}

/*
$db = new DBController();
$sql = "SELECT * FROM services WHERE service_id = :service_id";
$data = array('service_id' => 1);
echo $db->queryBind($sql, $data);
*/
/*
$db = new DBController();
$data['service_id']=1;
$data['service_name']='electric';
$db->bindKeys($data);
print_r($data);
*/
/*
header('Content-type: text/javascript');

$db = new DBController();
$users = $db->query("SELECT * FROM tbl_users WHERE username=?", array("ikkesh");
echo $users;

$db = new DBController();
$sql = "INSERT INTO `services`(`service_name`, `service_description`) VALUES (?, ?)";
echo $db->insertAutoId($sql, array('testservice', 'testdescription') );
*/
?>