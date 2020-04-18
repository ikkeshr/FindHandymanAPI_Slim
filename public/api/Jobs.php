<?php   

require_once ("DBController.php");

class Jobs {

    private $db;

    public function __construct() {
		$this->db = new DBController();
    }

    public function insertJob($data) {
        $sql = "INSERT INTO `jobs`(`service_id`, `title`, `description`,
                                    `date`, `time`, `address`, `address_lat`,
                                    `address_lng`, `budget`, `job_giver`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        //jobDetails = {title, description, budget, date, time, 
                //address, address_lat, address_lng, jobGiverId, service_id}
        $queryData = array(
            $data['service_id'], $data['title'], $data['description'],
            $data['date'], $data['time'], $data['address'],
            $data['address_lat'], $data['address_lng'], $data['budget'],
            $data['jobGiverId']
        );

        $job_id = $this->db->insertAutoId($sql, $queryData);

        $sql = "INSERT INTO `job_status`(`job_id`,`status`)
                 VALUES (?, ?)";

        $this->db->exec2($sql, array($job_id, 'posted') );
		
		return $job_id;
    }
	
	public function insertJobPictures($data) {
		$jobId = $data['jobId'];
		$pictureNames = $data['pictureNames'];
		
		$pictureNamesLength = count($pictureNames);
		if ( $pictureNamesLength > 0 ) {
			$sql = "INSERT INTO job_pictures (job_id, picture) VALUES "; // The space after VALUES is IMPORTANT
			for ($i=0; $i < $pictureNamesLength; $i++) {
				$sql .= "(".$jobId.", '".$pictureNames[$i]."')";
				if ($i < ($pictureNamesLength-1)){
					$sql .= ", ";
				}
			}
			$this->db->exec($sql);
		}
		return $sql;
    }
    
    public function deleteJob($data) {
        $job_id = $data['job_id'];
        $sql ="DELETE FROM `jobs` WHERE job_id=?";
        return $this->db->exec2($sql, array($job_id));
    }

    public function fetchMyJobs($data) {
        //data: userId, accountType, segment
        if ($data['accountType'] == "client") {
            return $this->fetchClientJobs($data);
        }
        else {
            return $this->fetchHandymanJobs($data);
        }
    }

    public function fetchClientJobs($data) {
        //data: userId, accountType, segment


        //current jobs
        $sql = "SELECT  j.job_id, j.title, j.address, DATE_FORMAT(j.date, '%D %b %Y') AS date, j.time, j.budget, js.status
                FROM    jobs j, job_status js
                WHERE   j.job_giver=?
                AND     j.job_id=js.job_id
                AND     js.status IN ('posted', 'booked', 'ongoing', 'reschedule')
                ORDER BY j.date DESC";


        //past jobs
        if ($data['segment'] == "pastJobs") {
            $sql = "SELECT  j.job_id, j.title, j.address, DATE_FORMAT(j.date, '%D %b %Y') AS date, j.time, j.budget, js.status
                FROM    jobs j, job_status js
                WHERE   j.job_giver=?
                AND     j.job_id=js.job_id
                AND     js.status IN ('completed', 'cancelled')
                ORDER BY j.date DESC";
        }
        return $this->db->query2( $sql, array($data['userId']) );
        
    }

    public function fetchHandymanJobs($data) {

        $sql = "SELECT j.job_id, j.title, j.address, DATE_FORMAT(j.date, '%D %b %Y') AS date, j.time, j.budget, js.status
                FROM jobs j, job_status js
                WHERE js.handyman_id = ?
                AND j.job_id=js.job_id
                AND js.status IN ('ongoing', 'reschedule')
                ORDER BY j.date DESC";

        if ($data['segment']  == "pastJobs") {
            $sql = "SELECT j.job_id, j.title, j.address, DATE_FORMAT(j.date, '%D %b %Y') AS date, j.time, j.budget, js.status
                FROM jobs j, job_status js
                WHERE js.handyman_id = ?
                AND j.job_id=js.job_id
                AND js.status IN ('completed', 'cancelled')
                ORDER BY j.date DESC";
        }

        return $this->db->query2( $sql, array($data['userId']) );

    }
	
	public function fetchJobDetails($data) {
		$retrieve = $data['retrieve'];
		$sql = "";
		
		if( $retrieve == "headerData") {
			$sql = "SELECT j.title, j.job_giver as jobGiverId, u.username as jobGiverName
					from jobs j, users u
					where j.job_id = ?
					and j.job_giver = u.uid";
		}
		else if ($retrieve == "descData") {
			$sql = "SELECT 	s.service_name, j.description
					FROM	services s, jobs j
					WHERE	j.job_id = ?
					AND		j.service_id = s.service_id";
		}
		else if ($retrieve == "infoData") {
			$sql = "SELECT j.address, j.address_lat, j.address_lng, j.date, DATE_FORMAT(j.time, '%k:%i') as time, j.budget, js.status, js.handyman_id, js.username as handyman_name
					FROM jobs j,
							(select * from job_status left join users on job_status.handyman_id = users.uid) js
					WHERE j.job_id = ?
					AND j.job_id = js.job_id";
		}
		else if ($retrieve == "galleryData") {
			$sql = "SELECT 	jp.picture
					FROM	jobs j, job_pictures jp
					WHERE	j.job_id = ?
					AND		j.job_id = jp.job_id";
		}
		
		return $this->db->query2( $sql, array($data['jobId']) );
    }

    public function fetch_handyman_contact($data) {
        $viewer_email_sql = "SELECT email as myemail from users where uid=?";
        $viewer_email = $this->db->query2($viewer_email_sql, array($data['uid']));
        $viewer_email = json_decode($viewer_email, true)[0]['myemail'];

        $uid = $data['job_id'];
        $sql = "SELECT 	u.email, u.phone
                FROM	users u, jobs j, job_status js
                WHERE	j.job_id=?
                AND		j.job_id = js.job_id
                AND		js.handyman_id = u.uid";
        $contact_info = json_decode($this->db->query2( $sql, array($data['job_id']) ),true)[0];
        $contact_info['myemail'] = $viewer_email;
        return json_encode($contact_info);
    }

    public function fetch_client_contact($data) {
        $viewer_email_sql = "SELECT email as myemail from users where uid=?";
        $viewer_email = $this->db->query2($viewer_email_sql, array($data['uid']));
        $viewer_email = json_decode($viewer_email, true)[0]['myemail'];

        $uid = $data['job_id'];
        $sql = "SELECT 	u.email, u.phone
                FROM	users u, jobs j
                WHERE	j.job_id=?
                AND		j.job_giver = u.uid";
        $contact_info = json_decode($this->db->query2( $sql, array($data['job_id']) ),true)[0];
        $contact_info['myemail'] = $viewer_email;
        return json_encode($contact_info);
    }

    public function fetchReviewPageData($data) {
        $sql = "SELECT	j.job_id, j.title as job_title,
                        clients.uid as client_id, clients.username as client_name, clients.picture as client_picture,
                        handymen.uid as handyman_id, handymen.username as handyman_name, handymen.picture as handyman_picture
                FROM	jobs j, users clients, users handymen, job_status js
                WHERE	j.job_id= ?
                AND		j.job_giver = clients.uid
                AND		j.job_id = js.job_id
                AND		js.handyman_id = handymen.uid";

        return $this->db->query2( $sql, array($data['job_id']) );
    }

    public function postReview($data) {
        $sql ="INSERT INTO `user_ratings`(`uid`, `job_id`, `rater_id`, `rating`, `review`, `date`)
                VALUES (?,?,?,?,?,?)";
        
        $reviewData = array($data['uid'], $data['job_id'], $data['rater_id'], $data['rating'], $data['review'], $data['date']);
        return $this->db->exec2($sql, $reviewData);
    }
    
    public function actionButtonData($data) {
        $sql = "SELECT j.job_id, js.status, j.job_giver, js.handyman_id, js.job_cancelled_by,
                        js.reschedule_date, DATE_FORMAT(js.reschedule_time, '%k:%i') as reschedule_time,
                        js.rescheduled_by, js.reschedule_reason
                FROM jobs j, job_status js 
                WHERE j.job_id = ?
                AND j.job_id = js.job_id";

        $ret = json_decode( $this->db->query2( $sql, array($data['jobId']) ), true )[0];
        
        if ($ret['status'] == 'completed' || $ret['status'] == 'cancelled') {
            $sql = "SELECT  COUNT(*) as reviewed 
                    FROM    user_ratings 
                    WHERE   rater_id = ?
                    AND     job_id = ?";
            $ret['job_giver_has_reviewed'] = json_decode( $this->db->query2( $sql, array($ret['job_giver'], $data['jobId']) ), true )[0]['reviewed'];
            
            $sql = "SELECT COUNT(*) as reviewed 
                    FROM    user_ratings 
                    WHERE   rater_id = ?
                    AND     job_id = ?";
            $ret['handyman_has_reviewed'] = json_decode( $this->db->query2( $sql, array($ret['handyman_id'], $data['jobId']) ), true )[0]['reviewed'];
        }

        return json_encode($ret);
    }

    public function getNotificationData($data) {
        $job_id = $data['job_id'];

        $sql = "SELECT	j.job_id, j.title as job_title, u1.username as client_name, u2.username as handyman_name, js.job_cancelled_by,
                        js.rescheduled_by
                FROM	jobs j, users u1, job_status js LEFT JOIN users u2 ON js.handyman_id = u2.uid
                WHERE	j.job_id = ?
                AND		j.job_id = js.job_id
                AND		j.job_giver = u1.uid";
        
        return $this->db->query2($sql, array($job_id));
    }

    public function bookHandyman($data) {
        $sql = "UPDATE 	job_status
                SET		handyman_id=?, status='booked'
                WHERE	job_id=?";
        
        $this->db->exec2($sql, array($data['handymanId'], $data['job_id']));
    }

    public function cancelBooking($data) {
        $job_id = $data['job_id'];
        $sql = "UPDATE 	job_status
                SET		handyman_id=null, status='posted'
                WHERE	job_id=?";
        $this->db->exec2($sql, array($job_id));
    }

    public function cancelJob($data) {
        $job_id = $data['job_id'];
        $account_type = $data['account_type'];

        $sql = "UPDATE  job_status
                SET     status='cancelled', job_cancelled_by=?
                WHERE   job_id=?";

        return $this->db->exec2($sql, array($account_type, $job_id));
    }

    public function nofitUpdate($data) {
        $job_id = $data['job_id'];
        
        $sqlExtra = "";
        if( isset($data['handymanId']) ) {
            $sqlExtra = " handyman_id = '" . $data['handymanId'] ."'";
        }
        else {
            $sqlExtra = ' handyman_id = null';
        }

        $sql = "UPDATE 	job_status
                SET		status=?, $sqlExtra 
                WHERE	job_id=?";

        $this->db->exec2($sql, array($data['status'], $job_id));
        //echo $sql;
    }

    public function reschedule_job($data) {
        $job_id = $data['job_id'];
        $reschedule_date = $data['date'];
        $reschedule_time = $data['time'];
        $rescheduled_by = $data['rescheduled_by'];
        $reschedule_reason = $data['reason'];

        $ret_data['status'] = 0;
        if (empty($job_id) || empty($reschedule_date) || empty($reschedule_time) ||
            empty($rescheduled_by) || empty($reschedule_reason)) {
                $ret_data['status'] = -1;
        }
        else {
            $sql ="UPDATE   job_status
                    SET     `reschedule_date`=?,`reschedule_time`=?,
                            `rescheduled_by`=?,`reschedule_reason`=?,
                            status='reschedule'
                    WHERE   job_id=?";
            
            $query_data = array($reschedule_date, $reschedule_time,
                                $rescheduled_by, $reschedule_reason,
                                $job_id);

            $this->db->exec2($sql, $query_data);
        }
        return json_encode($ret_data);
    }

    public function accept_reschedule($data) {
        $job_id = $data['job_id'];

        $sql = "UPDATE  jobs
                SET     date=(
                    SELECT  reschedule_date as date FROM job_status WHERE job_id=?
                ),
                time=(
                    SELECT  reschedule_time as time FROM job_status WHERE job_id=?
                )
                WHERE   job_id=?";

        //$new_schedule = json_decode($this->db->query2($sql, array($job_id)), true)[0];
        //return $new_schedule;
        $this->db->exec2($sql, array($job_id, $job_id, $job_id));

        $sql = "UPDATE  job_status
                SET     status='ongoing'
                WHERE   job_id=?";
        return $this->db->exec2($sql, array($job_id));
    }

    public function refuse_reschedule($data) {
        $job_id = $data['job_id'];

        $sql = "UPDATE  job_status
                SET     status='ongoing'
                WHERE   job_id=?";
        return $this->db->exec2($sql, array($job_id));
    }

}
/* 
$data['job_id'] = 31;
$data['uid'] = '8YaIg1j26dWMb9VbDNOo20q7lnM2';

$job = new Jobs();
$r = $job->accept_reschedule($data);
//echo $r;
print_r($r);

/* */
/* $job = new Jobs();
//$data['retrieve'] = "galleryData";
$data['job_id'] = 4;
$data['status'] = 'completed';
$data['handymanId'] = 'u8e2IgloVXNXAxsqwgv7tRjAWbC3';
$job->nofitUpdate($data); */


/*
$job = new Job();
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

echo $job->insertJob($data);
*/
/*
$job = new Jobs();
$data['jobId'] = 8;
$data['pictureNames'][0] = 'image1.jpg';
$data['pictureNames'][1] = 'image2.jpg';
$job->insertJobPictures($data);
*/
?>