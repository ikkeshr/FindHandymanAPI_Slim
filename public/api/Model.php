<?php

require_once ('DB.php');


Class Model {

    private $db;

    public function __construct() {
		$this->db = new DBController();
    }

    public function get_user($data) {
        $uid = $data['uid'];

        //Fetch user data
        $fetch_user_sql =   "SELECT uid, username, picture as profilePictureUrl, bio, 
                            type as account_type 
                            FROM users 
                            WHERE uid=?";
        $user = $this->db->query($fetch_user_sql, array($uid));

        //Fetch user addresses
        $fetch_user_addresses_sql = 'SELECT address, lat, lng 
                                    FROM user_addresses 
                                    WHERE uid=?';
        $user_addresses = $this->db->query($fetch_user_addresses_sql, array($uid));
        $user[0]['addresses'] = $user_addresses;

        //If the user is a handyman the following code will be executed
        $account_type = $user[0]['account_type'];
        if ($account_type == 'handyman') {
            //Fetch user services
            $user_services_sql = "SELECT	s.service_id, s.service_name, hs.start_price, hs.end_price
                                    FROM	handyman_services hs, services s
                                    WHERE	hs.handyman_id=?
                                    AND		hs.service_id = s.service_id";
            $user_services = $this->db->query($user_services_sql, array($uid));
            $user[0]['services'] = $user_services;

            //Fetch user availability
            $user_availability_sql = "SELECT day_name, start_time, end_time 
                                        FROM handyman_working_days_time 
                                        WHERE handyman_id = ?";
            $user_availability = $this->db->query($user_availability_sql, array($uid));
            $user[0]['availability'] = $user_availability;
        } // END IF

        return json_encode($user);

    }

    public function create_user($data) {
        //uid, username, bio, account_type
        //profilePictureUrl, addresses, services, availability

        if (!isset($data['uid']) || !isset($data['username']) || !isset($data['account_type'])) {
            return -1;
        }

        if (empty($data['uid']) || empty(trim($data['username'])) || empty($data['account_type'])) {
            return -1;
        }

        $uid = $data['uid'];
        $username = $data['username'];
        $account_type = $data['account_type'];

        $bio = NULL;
        if (isset($data['bio'])) {
            if ((!empty(trim($data['bio']))))
            $bio = $data['bio'];
        }

        $profilePictureUrl = "default-profile.png";
        if (isset($data['profilePictureUrl'])) {
            $profilePictureUrl = $data['profilePictureUrl'];
        }

        $addresses = [];
        if (isset($data['addresses'])) {
            $addresses = $data['addresses'];
        }

        if ($account_type == 'handyman') {
            $services = [];
            if (isset($data['services'])) {
                $services = $data['services'];
            }

            $availability = [];
            if (isset($data['availability'])) {
                $availability = $data['availability'];
            }
        }

        $this->db->beginTransaction();

        $create_user_sql = "INSERT INTO users (uid, username, bio, picture, type)
                    VALUES(?,?,?,?,?)";
        $create_user_data = array($uid, $username, $bio, $profilePictureUrl, $account_type);
        $create_user_sth = $this->db->exec($create_user_sql, $create_user_data);
        
        if ($create_user_sth != -2) {
            $rows_affected = $create_user_sth;

            if ( sizeof($addresses) > 0 ) {
                $sql = "INSERT INTO user_addresses (uid, address, lat, lng)
                            VALUES (?, ?, ?, ?)";
                            
                foreach ($addresses as $key => $value){
                    $address_data = array ( $uid, $value['address'], $value['lat'], $value['lng'] );
                    $inserted = $this->db->exec($sql, $address_data);
                    $rows_affected += $inserted;
                    //echo "inserted: " . $inserted . "</br>";
                    if ($inserted < 1) {
                        $this->db->rollBack();
                        return -2;
                    }
                }
            }

            if ( sizeof($services) > 0 ) {
                $sql = "INSERT INTO handyman_services (handyman_id, service_id, start_price, end_price)
                            VALUES (?, ?, ?, ?)";
                            
                foreach ($services as $key => $value){
                    $service_data = array ( $uid, $value['service_id'], $value['start_price'], $value['end_price'] );
                    $inserted = $this->db->exec($sql, $service_data);
                    $rows_affected += $inserted;
                    if ($inserted < 1) {
                        $this->db->rollBack();
                        return -2;
                    }
                }
            }

            if ( sizeof($availability) > 0 ) {
                $sql = "INSERT INTO handyman_working_days_time (handyman_id, day_name, start_time, end_time)
                            VALUES (?, ?, ?, ?)";
                            
                foreach ($availability as $key => $value){
                    $workingDays_data = array ( $uid, $value['day_name'], $value['start_time'], $value['end_time'] );
                    $inserted = $this->db->exec($sql, $workingDays_data);
                    $rows_affected += $inserted;
                    if ($inserted < 1) {
                        $this->db->rollBack();
                        return -2;
                    }
                }
            }

            $this->db->commit();
            return $rows_affected;
        }
        else {
            $this->db->rollBack();
            return -2;
        }



    }

    public function remove_user($data) {
        if (empty($data['uid'])) {
            return -1;
        }

        $uid = $data['uid'];

        $remove_user_sql = "DELETE FROM users
                            WHERE uid=?";

        $remove_user_sth = $this->db->exec($remove_user_sql, array($uid));
        return $remove_user_sth;
    }

    public function update_user($data) {
         //uid, username, bio, account_type
        //profilePictureUrl, addresses, services, availability

        if (empty($data['uid'])) {
            return -1;
        }

        $uid = $data['uid'];

        $this->db->beginTransaction();
        $rows_affected = 0;
        if (!empty($data['username'])) {
            $username = trim($data['username']);
            $sql = "UPDATE users SET username=? WHERE uid=?";
            $sth = $this->db->exec($sql, array($username, $uid));

            if ($sth > 0) {
                $rows_affected += $sth;
            }
            else {
                $this->db->rollBack();
                return -2;
            }
        }

        if (!empty($data['bio'])) {
            $bio = trim($data['bio']);
            $sql = "UPDATE users SET bio=? WHERE uid=?";
            $sth = $this->db->exec($sql, array($bio, $uid));

            if ($sth > 0) {
                $rows_affected += $sth;
            }
            else {
                $this->db->rollBack();
                return -2;
            }
        }

        if (!empty($data['profilePictureUrl'])) {
            $profilePictureUrl = trim($data['profilePictureUrl']);
            $sql = "UPDATE users SET picture=? WHERE uid=?";
            $sth = $this->db->exec($sql, array($profilePictureUrl, $uid));

            if ($sth > 0) {
                $rows_affected += $sth;
            }
            else {
                $this->db->rollBack();
                return -2;
            }
        }

        if (isset($data['addresses'])) {
            if (sizeof($data['addresses']) > 0) {
                //first remove all addresses of the user
                $sql = "DELETE FROM user_addresses WHERE uid=?";
                $sth = $this->db->exec($sql, array($uid));

                if ($sth >= 0) {
                    $this->db->rollBack();
                    return -2;
                }
                else {
                    //then insert new addresses
                    $addresses = $data['addresses'];
                    $sql = "INSERT INTO user_addresses (uid, address, lat, lng)
                            VALUES (?, ?, ?, ?)";
                            
                    foreach ($addresses as $key => $value){
                        $address_data = array ( $uid, $value['address'], $value['lat'], $value['lng'] );
                        $inserted = $this->db->exec($sql, $address_data);
                        $rows_affected += $inserted;
                        if ($inserted < 1) {
                            $this->db->rollBack();
                            return -2;
                        }
                    }
                }
            }
        }

        if (isset($data['services'])) {
            if (sizeof($data['services'])) {
                //first remove all services of the user
                $sql = "DELETE FROM handyman_services WHERE uid=?";
                $sth = $this->db->exec($sql, array($uid));

                if ($sth >= 0) {
                    $this->db->rollBack();
                    return -2;
                }
                else {
                    $services = $data['services'];
                    $sql = "INSERT INTO handyman_services (handyman_id, service_id, start_price, end_price)
                            VALUES (?, ?, ?, ?)";
                            
                    foreach ($services as $key => $value){
                        $service_data = array ( $uid, $value['service_id'], $value['start_price'], $value['end_price'] );
                        $inserted = $this->db->exec($sql, $service_data);
                        $rows_affected += $inserted;
                        if ($inserted < 1) {
                            $this->db->rollBack();
                            return -2;
                        }
                    }
                }
            }
        }

        if (isset($data['availability'])) {
            if (sizeof($data['availability']) > 0) {
                //first remove 
                $sql = "DELETE FROM handyman_working_days_time WHERE uid=?";
                $sth = $this->db->exec($sql, array($uid));

                if ($sth >= 0) {
                    $this->db->rollBack();
                    return -2;
                }
                else {
                    $availability = $data['availability'];
                    $sql = "INSERT INTO handyman_working_days_time (handyman_id, day_name, start_time, end_time)
                            VALUES (?, ?, ?, ?)";
                            
                    foreach ($availability as $key => $value){
                        $workingDays_data = array ( $uid, $value['day_name'], $value['start_time'], $value['end_time'] );
                        $inserted = $this->db->exec($sql, $workingDays_data);
                        $rows_affected += $inserted;
                        if ($inserted < 1) {
                            $this->db->rollBack();
                            return -2;
                        }
                    }
                }
            }
        }

        $this->db->commit();
        return $rows_affected;
    }

    public function get_services($data) {
        $offset = 0;
        $limit = 5;

        if (!empty($data['offset'])) {
            $offset = $data['offset'];
        }

        if (!empty($data['limit'])) {
            $limit = $data['limit'];
        }

        $sql = "SELECT  service_id, service_name, service_description
                FROM    services
                LIMIT   " . $offset . ", " . $limit;
        $services = $this->db->query($sql);
        return json_encode($services);
    }

    public function get_user_ratings($data) {
        if (empty($data['uid'])) {
            return -1;
        }

        $uid = $data['uid'];
        $offset = 0;
        $limit = 5;

        if (!empty($data['offset'])) {
            $offset = $data['offset'];
        }

        if (!empty($data['limit'])) {
            $limit = $data['limit'];
        }

        //Fetch Reviews
        $user_reviews_sql = "SELECT	ur.job_id, ur.rater_id, u.username as rater_username, 
                                    u.picture as rater_picture_url,
                                    ur.rating, ur.review, ur.date
                            FROM	user_ratings ur, users u
                            WHERE	ur.uid=?
                            AND		ur.rater_id = u.uid
                            ORDER BY ur.date DESC
                            LIMIT   " . $offset . ", " . $limit;
        $reviews = $this->db->query($user_reviews_sql, array($uid));
        $user_ratings[0]['reviews'] = $reviews;

        if ($offset == 0) {
            //Fetch Overall rating
            $fetch_overall_rating_sql = 'SELECT	ROUND(AVG(rating), 1) as overall_rating
            FROM	user_ratings
            WHERE	uid=?';
            $overall_rating = $this->db->query($fetch_overall_rating_sql, array($uid))[0]['overall_rating'];
            $user_ratings[0]['overall_rating'] = $overall_rating;

            //Fetch number of reviews
            $fetch_review_count_sql = 'SELECT	COUNT(*) as review_count
                    FROM	user_ratings
                    WHERE	uid=?';
            $overall_rating = $this->db->query($fetch_review_count_sql, array($uid))[0]['review_count'];
            $user_ratings[0]['review_count'] = $overall_rating;

            //Get user account type
            $account_type_sql = "SELECT type FROM users WHERE uid=?";
            $account_type = $this->db->query($account_type_sql, array($uid))[0]['type'];
            
            if ($account_type == 'handyman') {
                //Fetch user services rating
                $services_rating_sql = "SELECT      s.service_name, ROUND(AVG(ur.rating), 1) as rating
                FROM        user_ratings ur, jobs j, services s
                WHERE       ur.uid = ?
                AND         ur.job_id = j.job_id
                AND         j.service_id = s.service_id
                GROUP BY    s.service_id";
                $services_rating = $this->db->query($services_rating_sql, array($uid));
                $user_ratings[0]['services_rating'] = $services_rating;
            }
            
        }

        return json_encode($user_ratings);
    }

    public function create_review($data) {
        //Fields: uid(ratee_id), job_id, rater_id, rating, review, date
        if (empty($data['ratee_id']) || empty($data['job_id']) || empty($data['rater_id']) || 
                empty($data['rating']) || empty($data['review']) || empty($data['date'])){
            return -1;
        }

        $sql = "INSERT INTO user_ratings (uid, job_id, rater_id, rating, review, date)
                    VALUES(?,?,?,?,?,?)";
        $sql_data = array($data['ratee_id'], $data['job_id'], $data['rater_id'], $data['rating'],
                            $data['review'], $data['date']);
        $sth = $this->db->exec($sql, $sql_data);
        return $sth;
    }

    public function get_job($data) {
        $job_id = $data['job_id'];

        $job_sql = "SELECT	j.job_id, j.service_id, s.service_name, j.title, j.description, j.date, j.time,
                            DATE_FORMAT(j.time, '%k:%i') as time_pretty_format, j.address,
                            j.address_lat, j.address_lng, j.budget, 
                            j.job_giver as client_id, u.username as client_name, u.picture as client_profilePictureUrl,
                            js.*
                    FROM	jobs j, job_status js, users u, services s
                    WHERE	j.job_id=?
                    AND     j.service_id=s.service_id
                    AND		j.job_giver=u.uid
                    AND		j.job_id=js.job_id";
        $job = $this->db->query($job_sql, array($job_id));
        
        //Fetch handyman username and pictureUrl
        $handyman_id = $job[0]['handyman_id'];
        if (!empty($handyman_id)) {
            $handyman_sql = "SELECT username, picture FROM users WHERE uid=?";
            $handyman_data = $this->db->query($handyman_sql, [$handyman_id])[0];
            $job[0]['handyman_name'] = $handyman_data['username'];
            $job[0]['handyman_profilePictureUrl'] = $handyman_data['picture'];
        }

        //Fetch job pictures
        $job_pictures_sql = "SELECT picture FROM job_pictures WHERE job_id=?";
        $job_pictures = $this->db->query($job_pictures_sql, array($job_id));
        $job[0]['pictures'] = array_column($job_pictures, 'picture');

        return json_encode($job);
    }

    public function create_job($data) {
        if (empty($data['service_id']) || empty(trim($data['title']))) {
            return -1;
        }
        if (empty(trim($data['description'])) || empty($data['date']) || empty($data['time']) ) {
            return -1;
        }
        if (empty($data['address']) || empty($data['address_lat']) || empty($data['address_lng'])) {
            return -1;
        }
        if (empty($data['budget']) || empty($data['jobGiverId'])) {
            return -1;
        }

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

        $this->db->exec($sql, array($job_id, 'posted') );

        return $this->get_job($job_id); 
    }

    public function remove_job($data) {
        if (empty($data['job_id'])) {
            return -1;
        }

        $job_id = $data['job_id'];

        $sql = "DELETE FROM jobs WHERE job_id=?";

        $sth = $this->db->exec($sql, array($job_id));
        return $sth;
    }

    public function update_job($data) {
        if (empty($data['job_id'])) {
            return -1;
        }

        $job_id = $data['job_id'];

        //service_id, title, description, date, time, address, address_lat, address_lng
        //budget, job_giver(client_id), hadnyman_id, status, 
        //job_cancelled_by, reschedult_date, reschedule_time, rescheduled_by, reschedule_reason

        //Updatable fields: 
        //title, desription, date ,time, budget, handyman_id, status
        //job_cancelled_by, reschedult_date, reschedule_time, rescheduled_by, reschedule_reason

        $this->db->beginTransaction();
        $rows_affected = 0;

        if (isset($data['title'])) {
            if (!empty(trim($data['title']))) {
                $sql = "UPDATE jobs SET title=? WHERE job_id=?";
                $sth = $this->db->exec($sql, array($data['title'], $job_id));
                $rows_affected += $sth;
                if ($sth < 1) {
                    $this->db->rollBack();
                    return -2;
                }
            }
            else {
                $this->db->rollBack();
                return -1;
            }
        } //End title update

        if (isset($data['description'])) {
            if (!empty(trim($data['description']))) {
                $sql = "UPDATE jobs SET description=? WHERE job_id=?";
                $sth = $this->db->exec($sql, array($data['description'], $job_id));
                $rows_affected += $sth;
                if ($sth < 1) {
                    $this->db->rollBack();
                    return -2;
                }
            }
            else {
                $this->db->rollBack();
                return -1;
            }
        } //End description update

        if (isset($data['date'])) {
            if (!empty(trim($data['date']))) {
                $sql = "UPDATE jobs SET date=? WHERE job_id=?";
                $sth = $this->db->exec($sql, array($data['date'], $job_id));
                $rows_affected += $sth;
                if ($sth < 1) {
                    $this->db->rollBack();
                    return -2;
                }
            }
            else {
                $this->db->rollBack();
                return -1;
            }
        } //End date update

        if (isset($data['time'])) {
            if (!empty(trim($data['time']))) {
                $sql = "UPDATE jobs SET time=? WHERE job_id=?";
                $sth = $this->db->exec($sql, array($data['time'], $job_id));
                $rows_affected += $sth;
                if ($sth < 1) {
                    $this->db->rollBack();
                    return -2;
                }
            }
            else {
                $this->db->rollBack();
                return -1;
            }
        } //End time update

        if (isset($data['budget'])) {
            if (!empty($data['budget'])) {
                $sql = "UPDATE jobs SET budget=? WHERE job_id=?";
                $sth = $this->db->exec($sql, array($data['budget'], $job_id));
                $rows_affected += $sth;
                if ($sth < 1) {
                    $this->db->rollBack();
                    return -2;
                }
            }
            else {
                $this->db->rollBack();
                return -1;
            }
        } //End budget update

        if (isset($data['handyman_id'])) {
            if (!empty($data['handyman_id'])) {
                $sql = "UPDATE job_status SET handyman_id=? WHERE job_id=?";
                $sth = $this->db->exec($sql, array($data['handyman_id'], $job_id));
                $rows_affected += $sth;
                if ($sth < 0) {
                    $this->db->rollBack();
                    return -2;
                }
            }
            else {
                $this->db->rollBack();
                return -1;
            }
        } //End handyman_id update

        if (isset($data['status'])) {
            if (!empty(trim($data['status']))) {
                $sql = "UPDATE job_status SET status=? WHERE job_id=?";
                $sth = $this->db->exec($sql, array($data['status'], $job_id));
                $rows_affected += $sth;
                if ($sth < 1) {
                    $this->db->rollBack();
                    return -2;
                }
            }
            else {
                $this->db->rollBack();
                return -1;
            }
        } //End status update

        if (isset($data['job_cancelled_by'])) {
            if (!empty(trim($data['job_cancelled_by']))) {
                $sql = "UPDATE job_status SET job_cancelled_by=? WHERE job_id=?";
                $sth = $this->db->exec($sql, array($data['job_cancelled_by'], $job_id));
                $rows_affected += $sth;
                if ($sth < 1) {
                    $this->db->rollBack();
                    return -2;
                }
            }
            else {
                $this->db->rollBack();
                return -1;
            }
        } //End job_cancelled_by update

        if (isset($data['reschedule_date'])) {
            if (!empty($data['reschedule_date'])) {
                $sql = "UPDATE job_status SET reschedule_date=? WHERE job_id=?";
                $sth = $this->db->exec($sql, array($data['reschedule_date'], $job_id));
                $rows_affected += $sth;
                if ($sth < 1) {
                    $this->db->rollBack();
                    return -2;
                }
            }
            else {
                $this->db->rollBack();
                return -1;
            }
        } //End reschedule_date update

        if (isset($data['reschedule_time'])) {
            if (!empty($data['reschedule_time'])) {
                $sql = "UPDATE job_status SET reschedule_time=? WHERE job_id=?";
                $sth = $this->db->exec($sql, array($data['reschedule_time'], $job_id));
                $rows_affected += $sth;
                if ($sth < 1) {
                    $this->db->rollBack();
                    return -2;
                }
            }
            else {
                $this->db->rollBack();
                return -1;
            }
        } //End reschedule_time update

        if (isset($data['rescheduled_by'])) {
            if (!empty($data['rescheduled_by'])) {
                $sql = "UPDATE job_status SET rescheduled_by=? WHERE job_id=?";
                $sth = $this->db->exec($sql, array($data['rescheduled_by'], $job_id));
                $rows_affected += $sth;
                if ($sth < 1) {
                    $this->db->rollBack();
                    return -2;
                }
            }
            else {
                $this->db->rollBack();
                return -1;
            }
        } //End rescheduled_by update

        if (isset($data['reschedule_reason'])) {
            if (!empty($data['reschedule_reason'])) {
                $sql = "UPDATE job_status SET reschedule_reason=? WHERE job_id=?";
                $sth = $this->db->exec($sql, array($data['reschedule_reason'], $job_id));
                $rows_affected += $sth;
                if ($sth < 1) {
                    $this->db->rollBack();
                    return -2;
                }
            }
            else {
                $this->db->rollBack();
                return -1;
            }
        } //End reschedule_reason update

        $this->db->commit();
        return $rows_affected;
    }

    public function get_current_jobs($data) {
        if (empty($data['uid'])) {
            return -1;
        }

        $sql = "SELECT type FROM users WHERE uid=?";
        $account_type = $this->db->query($sql, [$data['uid']])[0]['type'];
        
        $sql = "";
        if ($account_type == 'client') {
            $sql = "SELECT  j.job_id, j.title, j.address, DATE_FORMAT(j.date, '%D %b %Y') AS date, j.time, j.budget, js.status
                    FROM    jobs j, job_status js
                    WHERE   j.job_giver=?
                    AND     j.job_id=js.job_id
                    AND     js.status IN ('posted', 'booked', 'ongoing', 'reschedule')
                    ORDER BY j.date DESC";
        }
        else if ($account_type == 'handyman') {
            $sql = "SELECT j.job_id, j.title, j.address, DATE_FORMAT(j.date, '%D %b %Y') AS date, j.time, j.budget, js.status
                    FROM jobs j, job_status js
                    WHERE js.handyman_id = ?
                    AND j.job_id=js.job_id
                    AND js.status IN ('ongoing', 'reschedule')
                    ORDER BY j.date DESC";
        }
        else {
            return "Invalid account_type for user:" . $data['uid'] . " in the database";
        }

        $sth = $this->db->query($sql, [$data['uid']]);
        return json_encode($sth);

    }

    public function get_past_jobs($data) {
        if (empty($data['uid'])) {
            return -1;
        }

        $sql = "SELECT type FROM users WHERE uid=?";
        $account_type = $this->db->query($sql, [$data['uid']])[0]['type'];

        $sql = "";
        if ($account_type == 'client') {
            $sql = "SELECT  j.job_id, j.title, j.address, DATE_FORMAT(j.date, '%D %b %Y') AS date, j.time, j.budget, js.status
                    FROM    jobs j, job_status js
                    WHERE   j.job_giver=?
                    AND     j.job_id=js.job_id
                    AND     js.status IN ('completed', 'cancelled')
                    ORDER BY j.date DESC";
        }
        else if ($account_type == 'handyman') {
            $sql = "SELECT j.job_id, j.title, j.address, DATE_FORMAT(j.date, '%D %b %Y') AS date, j.time, j.budget, js.status
                    FROM jobs j, job_status js
                    WHERE js.handyman_id = ?
                    AND j.job_id=js.job_id
                    AND js.status IN ('completed', 'cancelled')
                    ORDER BY j.date DESC";
        }
        else {
            return "Invalid account_type for user:" . $data['uid'] . " in the database";
        }

        $sth = $this->db->query($sql, [$data['uid']]);
        return json_encode($sth);

    }

}

//TESTS
/* $model = new Model();
$data['uid'] = 'cGgV5SV8VhabXe4d6AhxqKcYsG02';
print_r($model->get_current_jobs($data)); */

/* $data['uid'] = 'Wjfhkdsjdhfskdjhsdl';
echo $model->remove_user($data); */

/* $data['uid'] = 'cGgV5SV8VhabXe4d6AhxqKcYsG02';
$data['offset'] = 0;
$data['limit'] = 5;
$data['job_id'] = 24; */



/* $data['uid'] = 'Wjfhkdsjdhfskdjhsdl';
$data['username'] = 'testhandyman';
$data['bio'] = 'someBio written....';
$data['account_type'] = 'handyman';

$data['addresses'][0]['address'] = 'Grand Bois';
$data['addresses'][0]['lat'] = -20.4188774;
$data['addresses'][0]['lng'] = 57.5511401;

$data['addresses'][1]['address'] = 'Grand Bay';
$data['addresses'][1]['lat'] = -20.4188774;
$data['addresses'][1]['lng'] = 57.5511401;

$data['services'][0]['service_id'] = '14';
$data['services'][0]['start_price'] = 333;
$data['services'][0]['end_price'] = 999;

$data['services'][1]['service_id'] = '15';
$data['services'][1]['start_price'] = 222;
$data['services'][1]['end_price'] = 888;

$data['availability'][0]['day_name'] = 'Saturdays';
$data['availability'][0]['start_time'] = "13:19:08";
$data['availability'][0]['end_time'] = "12:19:08";

$data['availability'][1]['day_name'] = 'Sundays';
$data['availability'][1]['start_time'] = "13:19:08";
$data['availability'][1]['end_time'] = "12:19:08";

echo $model->create_user($data); */

/*
echo($model->get_user($data)); */
//echo $model->get_job($data);

?>