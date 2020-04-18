<?php

require_once ('DBController.php');

class Services {

	private $db;

    public function __construct() {
		$this->db = new DBController();
    }
	
	public function getOtherServices($servicesId) {
		
		$servicesIdString = "";
		$servicesIdLength = count($servicesId);
		for ($i=0; $i < $servicesIdLength; $i++){
		$servicesIdString .= $servicesId[$i];
			if ( $i != ($servicesIdLength-1) )
				$servicesIdString .= ",";
		}
		
		
		if ($servicesIdLength > 0) {
			$sql = "SELECT service_id, service_name 
					FROM services 
					WHERE service_id NOT IN ($servicesIdString)";
		} else {
			$sql = "SELECT service_id, service_name 
					FROM services";
		}
				
		return $this->db->query($sql);
	}
	
	public function getServices($data) {
		//LIMIT AND OFFSET DON"T WORK WELL WITH PREPARED STATEMENTS
		//https://stackoverflow.com/questions/10014147/limit-keyword-on-mysql-with-prepared-statement
		
		$limit = $data['limit'];
		$offset = $data['offset'];
		$language = "en";
		
		if (!empty($data['language'])) {
			$language = $data['language'];
		}

		if ($language == "en") {
			$sql = "SELECT service_id as id, service_name as name, service_description as description from services
				LIMIT $offset,$limit";
		}
		else if ($language == "fr") {
			$sql = "SELECT service_id as id, service_name_fr as name, service_description_fr as description from services
				LIMIT $offset,$limit";
		}

		echo $this->db->query($sql);
	}

	
}

//Testing...
/*
$service = new Services();
$data['limit'] = 5;
$data['offset'] = 0;
echo $service->getServices($data);
*/
?>