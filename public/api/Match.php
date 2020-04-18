<?php

require_once ("DBController.php");

class Match {

    private $db;

    public function __construct() {
		$this->db = new DBController();
    }
    
    public function fetchHandyman() {
        $sql = "SELECT u.uid, u.username, u.bio, 1 AS rating, 300 AS start_price, 900 AS end_price
                FROM users u
                WHERE u.type='handyman'";
        return $this->db->query($sql);
    }
	
	
	public function matchHandyman($data){
		//fetch job details
		$sql = "SELECT budget, time, address_lat, address_lng, service_id, date 
                 FROM jobs 
                 WHERE job_id = ?";
		
		$jobDetails = json_decode( $this->db->query2($sql, array($data['jobId'])), true )[0];
		
		//Fetch matched handymen
		$sql = "SELECT      hs.handyman_id, u.username, u.bio, hs.start_price, hs.end_price, 
									ROUND(MIN(HAVERSINE(:address_lat, :address_lng, ua.lat, ua.lng)),2) as distance,
									IFNULL( ROUND(AVG(ur.rating),1), 0) as rating, u.picture
				FROM        handyman_services hs LEFT JOIN user_ratings ur ON hs.handyman_id = ur.uid,
								users u, handyman_working_days_time hwdt, user_addresses ua
				WHERE       hs.service_id = :service_id
				AND         hs.handyman_id = u.uid
				AND         hs.handyman_id = hwdt.handyman_id
				AND         DAYNAME(:date) = SUBSTR(day_name, 1, CHAR_LENGTH(day_name)-1)
				AND         start_time <= :time
				AND         end_time >= :time
				AND         hs.start_price <= :budget
				AND         hs.handyman_id = ua.uid
				AND         HAVERSINE(:address_lat, :address_lng, ua.lat, ua.lng) < 8 
				GROUP BY    hs.handyman_id";
				
		return $this->db->queryBind($sql, $jobDetails);
				
		
	}

	public function matchHandyman2($data) {

		$sql ="SELECT  	hs.handyman_id, u.username, u.bio, u.picture, hs.start_price, hs.end_price,
						ROUND(HAVERSINE(ua.lat, ua.lng, j.address_lat, j.address_lng),1) as distance,
						IFNULL( ROUND(AVG(ur.rating),1), 0) as rating,
						MAX(MATCH_SCORE(
							hwdt.day_name, hwdt.start_time, hwdt.end_time, ua.lat, ua.lng, hs.start_price, hs.end_price,
							j.date, j.budget, j.address_lat, j.address_lng, j.time
						)) as score
				FROM    handyman_working_days_time hwdt, user_addresses ua, jobs j, users u,
						handyman_services hs LEFT JOIN user_ratings ur ON hs.handyman_id = ur.uid
				WHERE   j.job_id = ?
				AND     hs.service_id = j.service_id
				AND     hs.handyman_id = hwdt.handyman_id
				AND     hs.handyman_id = ua.uid
				AND     hs.handyman_id = u.uid
				GROUP BY hs.handyman_id
				ORDER BY score DESC";

		return $this->db->query2($sql, array($data['jobId']));
	}


}

/* $match = new Match();
$data['jobId'] = 2;
echo $ret = $match->matchHandyman2($data); */
//print_r( $ret );

/*
$match = new Match();
//jobDetails = {title, description, budget, date, time, 
                //address, address_lat, address_lng, jobGiverId, service_id}
$data['title'] = 'Test job 2';
$data['description'] = 'Test description';
$data['budget'] = '300';
$data['date'] = '2019-12-19';
$data['time'] = '15:05:02';
$data['address'] = 'Britannia, Mauritius';
$data['address_lat'] = '-20.4503000';
$data['address_lng'] = '57.5575000';
$data['jobGiverId'] = '8YaIg1j26dWMb9VbDNOo20q7lnM2';
$data['service_id'] = '1';

echo $match->insertJob($data);
*/

?>