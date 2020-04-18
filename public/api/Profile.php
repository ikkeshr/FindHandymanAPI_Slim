<?php 

require_once ('DBController.php');

class Profile {

    private $db;

    public function __construct() {
		$this->db = new DBController();
    }

    public function fetchProfileDetails($data) {
        $sql = "SELECT      u.username, IFNULL(u.bio, '') as bio, picture, ROUND(AVG(ur.rating), 1) as rating, COUNT(ur.rating) as ratingCount
                FROM        users u, user_ratings ur
                WHERE       u.uid = ?
                AND         u.uid = ur.uid";

        return $this->db->query2( $sql, array($data['uid']) );
    }


    public function fetchProfileInfo($data) {
        $sql = "SELECT      s.service_id, s.service_name, hs.start_price, hs.end_price
                FROM        users u, handyman_services hs, services s
                WHERE       u.uid = ?
                AND         u.uid = hs.handyman_id
                AND         hs.service_id = s.service_id";

        return $this->db->query2( $sql, array($data['uid']) );
    }

    
    public function fetchProfileServicesRating($data) {
        $sql = "SELECT      s.service_name, ROUND(AVG(ur.rating), 1) as rating
                FROM        user_ratings ur, jobs j, services s
                WHERE       ur.uid = ?
                AND         ur.job_id = j.job_id
                AND         j.service_id = s.service_id
                GROUP BY    s.service_id";

        return $this->db->query2( $sql, array($data['uid']) );
    }


    public function fetchProfileReviews($data) {
        $sql = "SELECT      u.username AS raterUsername, u.picture as raterPicture, ur.rating, ur.review, date
                FROM        user_ratings ur, users u
                WHERE       ur.uid = ?
                AND         ur.rater_id = u.uid";

        return $this->db->query2( $sql, array($data['uid']) );
    }
	
	public function fetchProfileAvailability($data) {
		$sql = "SELECT `day_name`, DATE_FORMAT(start_time,'%k:%i') as start_time, DATE_FORMAT(end_time,'%k:%i') as end_time
				FROM `handyman_working_days_time` 
				WHERE handyman_id = ?";
		return $this->db->query2( $sql, array($data['uid']) );
	}
	
	public function fetchUserAddresses($data) {
		$sql = "select address, lat, lng from user_addresses where uid = ?";
		return $this->db->query2( $sql, array($data['uid']) );
	}
	
	public function fetchUserWorkingInfo($data){
		$sql = "SELECT day_name, start_time, end_time 
				FROM handyman_working_days_time 
				WHERE handyman_id = ?";
				
		return $this->db->query2( $sql, array($data['uid']) );
    }	


}


//TESTS....
/* 
$profile = new Profile();
$data['job_id'] = '3';
echo $profile->fetch_client_contact($data);
 */ 
?>