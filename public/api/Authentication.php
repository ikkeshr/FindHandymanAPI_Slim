<?php

require_once ("DBController.php");

class Authentication {

    private $db;

    public function __construct() {
		$this->db = new DBController();
	}

	public function registerClient($data) {
		$uid = $data['uid'];
	
		$sql = "INSERT INTO users (uid, username, type) VALUES (?, ?, ?)";
		$user_data = array( $uid, $data['username'], 'client' );
		$this->db->exec2( $sql, $user_data );
		
		//Set profile picture
		$this->setProfilePicture($uid,$data);
		

		//Insert user addresses in user_addresses table
		$this->insertAddresses($uid, $data['addresses']);
	}
	
	/*public function setProfilePicture($uid, $pictureName) {
		$sql = "UPDATE 	users
				SET 	picture = ?
				WHERE 	uid = ?";
		$this->db->exec2($sql, array($pictureName, $uid));
	}*/
	
	public function setProfilePicture($data) {
		$sql = "UPDATE 	users
				SET 	picture = ?
				WHERE 	uid = ?";
		
		$pictureName = "default-profile.png";
		if ( isset($data['picture']) ) {
			$pictureName = $data['picture'];
		}
		
		$this->db->exec2($sql, array($pictureName, $data['uid']));
	}

    public function registerHandyman($data) {

        $uid = $data['uid'];

        //Insert user details in users table
        $sql = "INSERT INTO users (uid, username, bio, type) VALUES (?, ?, ?, ?)";
		$user_data = array( $uid, $data['username'], $data['bio'], 'handyman' );
        $this->db->exec2( $sql, $user_data );

        //Insert user addresses in user_addresses table
		$this->insertAddresses($uid, $data['addresses']);

        //Insert user services in handyman_services table
        $this->insertServices($uid, $data['services']);

        //Insert user working days in handyman_working_days_time
		$this->insertWorkingDays($uid, $data['workingDays']);
		
		//Set profile picture
		$this->setProfilePicture($uid,$data);
    }

    private function insertAddresses($uid, $addresses) {
		if ( sizeof($addresses) > 0 ) {
			$sql = "INSERT INTO user_addresses (uid, address, lat, lng)
						VALUES (?, ?, ?, ?)";
						
			foreach ($addresses as $key => $value){
				$address_data = array ( $uid, $value['address'], $value['lat'], $value['lng'] );
				$this->db->exec2($sql, $address_data);
			}
		}
    }

    private function insertServices($uid, $services) {
		if ( sizeof($services) > 0 ) {
			$sql = "INSERT INTO handyman_services (handyman_id, service_id, start_price, end_price)
						VALUES (?, ?, ?, ?)";
						
			foreach ($services as $key => $value){
				$service_data = array ( $uid, $value['id'], $value['start_price'], $value['end_price'] );
				$this->db->exec2($sql, $service_data);
			}
		}
    }

    private function insertWorkingDays($uid, $workingDays) {
		if ( sizeof($workingDays) > 0 ) {
			$sql = "INSERT INTO handyman_working_days_time (handyman_id, day_name, start_time, end_time)
						VALUES (?, ?, ?, ?)";
						
			foreach ($workingDays as $key => $value){
				$workingDays_data = array ( $uid, $value['day'], $value['start_time'], $value['end_time'] );
				$this->db->exec2($sql, $workingDays_data);
			}
		}
	}
	
	public function editProfile($data) {

		if (!empty($data['username'])) {
			$sql = "UPDATE users SET username=? WHERE uid=?";
			$this->db->exec2($sql, array($data['username'], $data['uid']));
		}

		if (!empty($data['picture'])) {
			$sql = "UPDATE users SET picture=? WHERE uid=?";
			$this->db->exec2($sql, array($data['picture'], $data['uid']));
		}

		if (!empty($data['bio'])) {
			$sql = "UPDATE users SET bio=? WHERE uid=?";
			$this->db->exec2($sql, array($data['bio'], $data['uid']));
		}


		$sql = "DELETE FROM user_addresses WHERE uid=?";
		$this->db->exec2($sql, array($data['uid']));
		$this->insertAddresses($data['uid'], $data['addresses']);

		$sql = "DELETE FROM handyman_services WHERE handyman_id=?";
		$this->db->exec2($sql, array($data['uid']));
		$this->insertServices($data['uid'], $data['services']);

		$sql = "DELETE FROM handyman_working_days_time WHERE handyman_id=?";
		$this->db->exec2($sql, array($data['uid']));
		$this->insertWorkingDays($data['uid'], $data['workingDays']);
	}

	//data: uid, username, email, phone, account_type
	public function create_user($data) {
		$sql = "INSERT INTO users (uid, username, email, phone, type, picture)
				VALUES (?,?,?,?,?,?)";
		
		$default_profile_pic = "default-profile.png";

		$query_data = array(
			$data['uid'], $data['username'], $data['email'], $data['phone'], 
			$data['account_type'], $default_profile_pic
		);

		return $this->db->exec2($sql, $query_data);
	}

/* 	
	public function fixDataForm($data) {

		$servicesLength = sizeof($data['services']);
		for ($i=0; $i<$servicesLength; $i++) {
			$data['services'][$i]['id'] = $data['services'][$i]['service_id'];
			unset($data['services'][$i]['service_id']);
		}

		$wdLength = sizeof($data['workingDays']);
		for ($i=0; $i<$wdLength; $i++) {
			$data['workingDays'][$i]['day'] = $data['workingDays'][$i]['day_name'];
			unset($data['workingDays'][$i]['day_name']);
		}

		return $data;
	}
 */
    

}

/* 
$data['uid'] ="aaaaaaa";
$data['username'] = "testaaaa";
$data['email'] = "test@email.com";
$data['phone'] = "123456";
$data['account_type'] = "client";

$auth = new Authentication();

$r = $auth->create_user($data);

echo $r;
 */
//Tests........


/*
$data['uid'] = 'Wjfhkdsjdhfskdjhsdl';
$data['username'] = 'testhandyman';
$data['bio'] = 'someBio written....';

$data['addresses'][0]['address'] = 'Grand Bois';
$data['addresses'][0]['address_lat'] = -20.4188774;
$data['addresses'][0]['address_lng'] = 57.5511401;

$data['addresses'][1]['address'] = 'Grand Bay';
$data['addresses'][1]['address_lat'] = -20.4188774;
$data['addresses'][1]['address_lng'] = 57.5511401;

$data['services'][0]['service_id'] = '14';
$data['services'][0]['start_price'] = 333;
$data['services'][0]['end_price'] = 999;

$data['services'][1]['service_id'] = '15';
$data['services'][1]['start_price'] = 222;
$data['services'][1]['end_price'] = 888;

$data['workingDays'][0]['day_name'] = 'Saturdays';
$data['workingDays'][0]['start_time'] = "13:19:08";
$data['workingDays'][0]['end_time'] = "12:19:08";

$data['workingDays'][1]['day_name'] = 'Sundays';
$data['workingDays'][1]['start_time'] = "13:19:08";
$data['workingDays'][1]['end_time'] = "12:19:08";



$auth = new Authentication();
$temp = $auth->fixDataForm($data);
print("<pre>".print_r($temp,true)."</pre>");
*/


?>