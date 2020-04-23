<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app = new \Slim\App;

$app->get('/job/{id}', function(Request $request, Response $response){
    // AUTHENTICATION
    // $auth = new Authentication();
    // $verifiedUser = $auth->authenticate($request);
    // if ($verifiedUser['status']['code'] != 200) {
    //     return $response->withStatus($verifiedUser['status']['code']);
    // }

    $job_id = $request->getAttribute('id');

    $params = $request->getQueryParams();

    // service name language
    $service_name = "service_name";
    if (!empty($params['lang'])) {
        if ($params['lang'] == "fr") {
            $service_name = "service_name_fr";
        }
    }

    $db = new DB();

    $job_sql = "SELECT	j.job_id, j.service_id, s.$service_name as service_name, j.title, j.description, j.date, j.time,
                        DATE_FORMAT(j.time, '%k:%i') as time_pretty_format, j.address,
                        j.address_lat, j.address_lng, j.budget, 
                        j.job_giver as client_id, u.username as client_name, u.picture as client_profilePictureUrl,
                        js.*, j.online_payment_made
                FROM	jobs j, job_status js, users u, services s
                WHERE	j.job_id=?
                AND     j.service_id=s.service_id
                AND		j.job_giver=u.uid
                AND		j.job_id=js.job_id";
    $job = $db->query($job_sql, [$job_id])['data'];

    // Convert the first character of the job status text's
    $job[0]['display_status'] = ucfirst($job[0]['status']);

    //Fetch handyman username and pictureUrl
    $handyman_id = $job[0]['handyman_id'];
    if (!empty($handyman_id)) {
        $handyman_sql = "SELECT username, picture FROM users WHERE uid=?";
        $handyman_data = $db->query($handyman_sql, [$handyman_id])['data'][0];
        $job[0]['handyman_name'] = $handyman_data['username'];
        $job[0]['handyman_profilePictureUrl'] = $handyman_data['picture'];

        $sql = "SELECT stripe_account_id FROM handymen_stripe_account WHERE handyman_id=?";
        $stripe_account_id = $db->query($sql, [$handyman_id])['data'][0]['stripe_account_id'];
        $job[0]['handyman_stripe_account_id'] = $stripe_account_id;

    }

    //Fetch job pictures
    $job_pictures_sql = "SELECT picture FROM job_pictures WHERE job_id=?";
    $job_pictures = $db->query($job_pictures_sql, [$job_id])['data'];
    $job[0]['pictures'] = array_column($job_pictures, 'picture');

    // FETCH JOB PICTURES URL
    $storage = new Storage();
    $JOBPICTURESURL = [];
    foreach ($job[0]['pictures'] as $pictureName) {
        $pictureUrl = $storage->getUrl('images/jobpictures/' . $pictureName);
        array_push($JOBPICTURESURL, $pictureUrl);
    }
    $job[0]['picturesUrl'] = $JOBPICTURESURL;

    return $response->withJson($job);
});

//BODY = {title, description, budget, date, time, 
//          address, address_lat, address_lng, jobGiverId, service_id, picturesUrl[]}
$app->post('/job', function(Request $request, Response $response){
    //AUTHENTICATION
    $auth = new Authentication();
    $verifiedUser = $auth->authenticate($request);
    if ($verifiedUser['status']['code'] != 200) {
        return $response->withStatus($verifiedUser['status']['code']);
    }
    
    $body = $request->getParsedBody();

    // Check if all the necessary data is in the request body
    if (!isset($body['service_id']) || 
        !isset($body['title']) ||
        !isset($body['description']) ||
        !isset($body['date']) || 
        !isset($body['time']) ||
        !isset($body['address']) ||
        !isset($body['address_lat']) || 
        !isset($body['address_lng']) ||
        !isset($body['budget']) ||
        !isset($body['jobGiverId'])) {
            return $response->withJson('Missing data in request body')->withStatus(400);   
        }
    
    // Validate the data in the request body 
    if (empty($body['service_id']) || empty(trim($body['title']))) {
        return $response->withStatus(400);
    }
    if (empty(trim($body['description'])) || empty($body['date']) || empty($body['time']) ) {
        return $response->withStatus(400);
    }
    if (empty($body['address'])) {
        return $response->withJson($body)->withStatus(400);
    }
    if (empty($body['budget']) || empty($body['jobGiverId'])) {
        return $response->withStatus(400);
    }

    $db = new DB();
    //$db->beginTransaction();
    // Insert the job
    $sql = "INSERT INTO `jobs`(`service_id`, `title`, `description`,
                                `date`, `time`, `address`, `address_lat`,
                                `address_lng`, `budget`, `job_giver`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    //jobDetails = {title, description, budget, date, time, 
            //address, address_lat, address_lng, jobGiverId, service_id, picturesUrl[]}
    $queryData = array(
        $body['service_id'], $body['title'], $body['description'],
        $body['date'], $body['time'], $body['address'],
        $body['address_lat'], $body['address_lng'], $body['budget'],
        $body['jobGiverId']
    );

    $sth = $db->insertAutoId($sql, $queryData);

    if ($sth['status']['code'] != 200) {
        return $response->withStatus($sth['status']['code']);
    }

    $job_id = $sth['data']['lastInsertId'];

    // Insert job pictures
    if (isset($body['picturesUrl'])) {
        $picturesUrl = $body['picturesUrl'];
        $picturesUrl_count = sizeof($picturesUrl);
        if ($picturesUrl_count > 0) {
            $sql = "INSERT INTO job_pictures (job_id, picture) VALUES" . " ";
            $sqlExtra = "(?,?),";
            $sql_data = [];

            for ($i=0; $i < $picturesUrl_count; $i++) {
                if ($i == ($picturesUrl_count-1)) {
                    $sqlExtra = "(?,?)";
                }
                $sql .= $sqlExtra;
                array_push($sql_data, $job_id, $picturesUrl[$i]);
            }
            $db->exec($sql, $sql_data);
        }
    }

    // Insert the job status
    $sql = "INSERT INTO `job_status`(`job_id`,`status`)
             VALUES (?, ?)";
    $db->exec($sql, array($job_id, 'posted') );

    //$db->commit();

    return $response->withStatus(200); 
});


$app->put('/job/{id}', function(Request $request, Response $response){
    //AUTHENTICATION
    $auth = new Authentication();
    $verifiedUser = $auth->authenticate($request);
    if ($verifiedUser['status']['code'] != 200) {
        return $response->withStatus($verifiedUser['status']['code']);
    }
    $job_id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    //service_id, title, description, date, time, address, address_lat, address_lng
    //budget, job_giver(client_id), hadnyman_id, status, 
    //job_cancelled_by, reschedult_date, reschedule_time, rescheduled_by, reschedule_reason

    //Updatable fields: 
    //title, desription, date ,time, budget, handyman_id, status
    //job_cancelled_by, reschedult_date, reschedule_time, rescheduled_by, reschedule_reason
    $db = new DB();
    $db->beginTransaction();
    $rows_affected = 0;

    if (isset($body['title'])) {
        if (!empty(trim($body['title']))) {
            $sql = "UPDATE jobs SET title=? WHERE job_id=?";
            $sth = $db->exec($sql, array($body['title'], $job_id));
        }
        else {
            $db->rollBack();
            return $response->withStatus(400);
        }
    } //End title update

    if (isset($body['description'])) {
        if (!empty(trim($body['description']))) {
            $sql = "UPDATE jobs SET description=? WHERE job_id=?";
            $sth = $db->exec($sql, array($body['description'], $job_id));
        }
        else {
            $db->rollBack();
            return $response->withStatus(400);
        }
    } //End description update

    if (isset($body['date'])) {
        if (!empty(trim($body['date']))) {
            $sql = "UPDATE jobs SET date=? WHERE job_id=?";
            $sth = $db->exec($sql, array($body['date'], $job_id));
        }
        else {
            $db->rollBack();
            return $response->withStatus(400);
        }
    } //End date update

    if (isset($body['time'])) {
        if (!empty(trim($body['time']))) {
            $sql = "UPDATE jobs SET time=? WHERE job_id=?";
            $sth = $db->exec($sql, array($body['time'], $job_id));
        }
        else {
            $db->rollBack();
            return $response->withStatus(400);
        }
    } //End time update

    if (isset($body['budget'])) {
        if (!empty($body['budget'])) {
            $sql = "UPDATE jobs SET budget=? WHERE job_id=?";
            $sth = $db->exec($sql, array($body['budget'], $job_id));
        }
        else {
            $db->rollBack();
            return $response->withStatus(400);
        }
    } //End budget update

    if (isset($body['handyman_id'])) {
        if (!empty($body['handyman_id'])) {
            $sql = "UPDATE job_status SET handyman_id=? WHERE job_id=?";
            $sth = $db->exec($sql, array($body['handyman_id'], $job_id));
        }
        else {
            $db->rollBack();
            return $response->withStatus(400);
        }
    } //End handyman_id update

    if (isset($body['status'])) {
        if (!empty(trim($body['status']))) {
            $sql = "UPDATE job_status SET status=? WHERE job_id=?";
            $sth = $db->exec($sql, array($body['status'], $job_id));
        }
        else {
            $db->rollBack();
            return $response->withStatus(400);
        }
    } //End status update

    if (isset($body['job_cancelled_by'])) {
        if (!empty(trim($body['job_cancelled_by']))) {
            $sql = "UPDATE job_status SET job_cancelled_by=? WHERE job_id=?";
            $sth = $db->exec($sql, array($body['job_cancelled_by'], $job_id));
        }
        else {
            $db->rollBack();
            return $response->withStatus(400);
        }
    } //End job_cancelled_by update

    if (isset($body['reschedule_date'])) {
        if (!empty($body['reschedule_date'])) {
            $sql = "UPDATE job_status SET reschedule_date=? WHERE job_id=?";
            $sth = $db->exec($sql, array($body['reschedule_date'], $job_id));
        }
        else {
            $db->rollBack();
            return $response->withStatus(400);
        }
    } //End reschedule_date update

    if (isset($body['reschedule_time'])) {
        if (!empty($body['reschedule_time'])) {
            $sql = "UPDATE job_status SET reschedule_time=? WHERE job_id=?";
            $sth = $db->exec($sql, array($body['reschedule_time'], $job_id));
        }
        else {
            $db->rollBack();
            return $response->withStatus(400);;
        }
    } //End reschedule_time update

    if (isset($body['rescheduled_by'])) {
        if (!empty($body['rescheduled_by'])) {
            $sql = "UPDATE job_status SET rescheduled_by=? WHERE job_id=?";
            $sth = $db->exec($sql, array($body['rescheduled_by'], $job_id));
        }
        else {
            $db->rollBack();
            return $response->withStatus(400);
        }
    } //End rescheduled_by update

    if (isset($body['reschedule_reason'])) {
        if (!empty($body['reschedule_reason'])) {
            $sql = "UPDATE job_status SET reschedule_reason=? WHERE job_id=?";
            $sth = $db->exec($sql, array($body['reschedule_reason'], $job_id));
        }
        else {
            $db->rollBack();
            return $response->withStatus(400);
        }
    } //End reschedule_reason update

    $db->commit();
    return $response->withStatus(200);
});


$app->delete('/job/{id}', function(Request $request, Response $response){
    //AUTHENTICATION
    $auth = new Authentication();
    $verifiedUser = $auth->authenticate($request);
    if ($verifiedUser['status']['code'] != 200) {
        return $response->withStatus($verifiedUser['status']['code']);
    }

    $job_id = $request->getAttribute('id');
    
    $db = new DB();
    $sql = "DELETE FROM jobs WHERE job_id=?";
    $sth = $db->exec($sql, [$job_id]);

    return $response->withStatus($sth['status']['code']);
});


$app->get('/job/{id}/match', function(Request $request, Response $response){
    //AUTHENTICATION
    // $auth = new Authentication();
    // $verifiedUser = $auth->authenticate($request);
    // if ($verifiedUser['status']['code'] != 200) {
    //     return $response->withStatus($verifiedUser['status']['code']);
    // }
    
    $job_id = $request->getAttribute('id');
    
    $db = new DB();

    $sql = "SELECT date, time FROM jobs WHERE job_id=?";
    $job = $db->query($sql, [$job_id])['data'];

    if (sizeof($job) == 0) {
        return $response->withStatus(204); // No Content
    }
    
    // Exclude handymen who have other jobs schedule on the date
    $sql = "SELECT  js.handyman_id
            FROM    jobs j, job_status js
            WHERE   j.date = ?
            AND     (SUBTIME(j.time, '03:00') < ? AND ADDTIME(j.time, '03:00') >= ?)
            AND     j.job_id = js.job_id
            AND     js.status = 'ongoing'";
    
    $excludeHandymanObjArr = $db->query($sql, [$job[0]['date'], $job[0]['time'], $job[0]['time']])['data'];
    $excludeHandymanSQLString = "";

    if (sizeof($excludeHandymanObjArr) > 0) {
        $excludeHandymanArr = array_column($excludeHandymanObjArr, 'handyman_id');
        $excludeHandymanSQLString = "AND hs.handyman_id NOT IN (".implode(',',array_map(array($db, 'quote'), $excludeHandymanArr)).")" . " ";
        # https://arjunphp.com/array_map-class-method-php/
        # https://stackoverflow.com/questions/10490860/php-implode-but-wrap-each-element-in-quotes
    }

    
    $sql ="SELECT  hs.handyman_id, u.username, u.bio, u.picture, hs.start_price, hs.end_price,
                ROUND(HAVERSINE(ua.lat, ua.lng, j.address_lat, j.address_lng),1) as distance,
                IFNULL( ROUND(AVG(ur.rating),1), 0) as rating,
                            MAX(MATCH_SCORE(
                                hwdt.day_name, hwdt.start_time, hwdt.end_time, ua.lat, ua.lng, hs.start_price, hs.end_price,
                                j.date, j.budget, j.address_lat, j.address_lng, j.time
                            )) as score
            FROM    user_addresses ua, jobs j, users u,
                (handyman_services hs LEFT JOIN user_ratings ur ON hs.handyman_id = ur.uid)
                LEFT JOIN handyman_working_days_time hwdt ON hs.handyman_id = hwdt.handyman_id
            WHERE   j.job_id = 1
            AND     hs.service_id = j.service_id
            $excludeHandymanSQLString
            AND     hs.handyman_id = ua.uid
            AND     hs.handyman_id = u.uid
            GROUP BY hs.handyman_id
            ORDER BY score DESC";

	$matchedHandymen = $db->query($sql, [$job_id]);
    return $response->withJson($matchedHandymen['data'])->withStatus($matchedHandymen['status']['code']);
});

$app->get('/job/{userId}/current', function(Request $request, Response $response){
    //AUTHENTICATION
    $auth = new Authentication();
    $verifiedUser = $auth->authenticate($request);
    if ($verifiedUser['status']['code'] != 200) {
        return $response->withStatus($verifiedUser['status']['code']);
    }
    

    $user_id = $request->getAttribute('userId');
    
    $db = new DB();
    $sql = "SELECT type FROM users WHERE uid=?";
    $account_type = $db->query($sql, [$user_id])['data'][0]['type'];
    
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

    $sth = $db->query($sql, [$user_id]);
    return $response->withJson($sth['data'])->withStatus($sth['status']['code']);
});

$app->get('/job/{userId}/past', function(Request $request, Response $response){
    //AUTHENTICATION
    $auth = new Authentication();
    $verifiedUser = $auth->authenticate($request);
    if ($verifiedUser['status']['code'] != 200) {
        return $response->withStatus($verifiedUser['status']['code']);
    }
    

    $user_id = $request->getAttribute('userId');
    
    $db = new DB();
    $sql = "SELECT type FROM users WHERE uid=?";
    $account_type = $db->query($sql, [$user_id])['data'][0]['type'];

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

    $sth = $db->query($sql, [$user_id]);
    return $response->withJson($sth['data'])->withStatus($sth['status']['code']);
});




$app->get('/job/v2/{id}', function(Request $request, Response $response){
    $job_id = $request->getAttribute('id');
    $params = $request->getQueryParams();

    $db = new DB();

    $datetimePrettyFormatExtra = "at";
    if (!empty($params['lang'])) {
        if ($params['lang'] == "fr") {
            //$db->exec("SET NAMES 'utf8'");
            $db->exec("SET lc_time_names = 'fr_FR'");
            $datetimePrettyFormatExtra = "à";
        }
    }

    // FETCH JOB DATA
    $sql = "SELECT  j.job_id as jobId, j.title, j.description, j.budget, js.status,
                    j.date, DATE_FORMAT(j.date, '%a %D %b %Y') as datePrettyFormat,
                    j.time, TIME_FORMAT(j.time, '%h:%i %p') as timePrettyFormat,
                    CONCAT(j.date,' ', j.time) as datetime,
                    DATE_FORMAT(CONCAT(j.date,' ', j.time), '%a %D %b %Y $datetimePrettyFormatExtra %h:%i %p') as datetimePrettyFormat
            FROM    jobs j, job_status js
            WHERE   j.job_id=? AND j.job_id=js.job_id";
    $JOB = $db->query($sql, [$job_id])['data'];

    // Convert the first character of the job status text's
    $JOB[0]['status'] = ucfirst($JOB[0]['status']);

    // Convert budget into a readable amount format
    $JOB[0]['budget'] = "Rs " . number_format($JOB[0]['budget'], 2);

    // Add a job date in the timeAgo format
    // $datetime = $JOB[0]['datetime'];
    // $timeAgo = Technodelight\TimeAgo::withTranslation(new \DateTime($datetime), 'en');
    // if (!empty($params['lang'])) {
    //     if ($params['lang'] == "fr") {
    //         $timeAgo = Technodelight\TimeAgo::withTranslation(new \DateTime($datetime), 'fr');
    //     }
    // }
    // $JOB[0]['datetimeTimeAgoFormat'] = $timeAgo->inWords();


    // FETCH SERVICE DATA
    $sql = "SELECT  service_id FROM jobs WHERE job_id=?";
    $service_id = $db->query($sql, [$job_id])['data'][0]['service_id'];

    $sql = "SELECT  service_id as id, service_name_en as name, service_description_en as description
            FROM    services WHERE service_id=?";
    if (!empty($params['lang'])) {
        if ($params['lang'] == "fr") {
            $sql = "SELECT  service_id as id, service_name_fr as name, service_description_fr as description
            FROM    services WHERE service_id=?";
        }
    }
    $SERVICE = $db->query($sql, [$service_id])['data'][0];
    $JOB[0]['service'] = $SERVICE;

    // FETCH ADDRESS
    $sql = "SELECT  address, address_lat as lat, address_lng as lng
            FROM    jobs WHERE job_id=?";
    $ADDRESS = $db->query($sql, [$job_id])['data'][0];
    $JOB[0]['address'] = $ADDRESS;

    // FETCH CLIENT DATA
    $sql = "SELECT  u.uid as id, u.username, u.picture as profilePictureName
            FROM    users u, jobs j
            WHERE   j.job_id=?
            AND     j.job_giver=u.uid";
    $CLIENT = $db->query($sql, [$job_id])['data'][0];
    $JOB[0]['client'] = $CLIENT;

    // FETCH HANDYMAN DATA
    $sql = "SELECT  u.uid as id, u.username, u.picture as profilePictureName
            FROM    users u RIGHT JOIN job_status js ON js.handyman_id=u.uid
            WHERE   js.job_id=?";
    $HANDYMAN = $db->query($sql, [$job_id])['data'][0];
    $JOB[0]['handyman'] = $HANDYMAN;

    // FETCH JOB PICTURES NAME
    $sql = "SELECT picture FROM job_pictures WHERE job_id=?";
    $JOBPICTURES = $db->query($sql, [$job_id])['data'];
    $JOB[0]['picturesName'] = array_column($JOBPICTURES, 'picture');

    // FETCH JOB PICTURES URL
    $storage = new Storage();
    $JOBPICTURESURL = [];
    foreach ($JOB[0]['picturesName'] as $pictureName) {
        $pictureUrl = $storage->getUrl('images/jobpictures/' . $pictureName);
        array_push($JOBPICTURESURL, $pictureUrl);
    }
    $JOB[0]['picturesUrl'] = $JOBPICTURESURL;


    // FETCH JOB ACTIONS
    $sql = "SELECT  job_cancelled_by, reschedule_date, reschedule_time, rescheduled_by, reschedule_reason
            FROM    job_status js WHERE job_id = ?";
    $JOBACTIONS = $db->query($sql, [$job_id])['data'][0];
    $JOB[0]['actions'] = $JOBACTIONS;

    return $response->withJson($JOB);
});


?>