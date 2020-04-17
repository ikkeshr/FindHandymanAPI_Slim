<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app = new \Slim\App;


$app->get('/user/{id}', function(Request $request, Response $response){
    //return getUser($request, $response);
    $res = getUser($request, $response);
    return $response->withJson($res['data'])->withStatus($res['status']['code']);
});

//BODY: uid, username, account_type, profilePictureUrl?
$app->post('/user', function(Request $request, Response $response){
    //AUTHENTICATION ALREADY CARRIED OUT BY 'getUser' FUNCTION
    // $auth = new Authentication();
    // $verifiedUser = $auth->authenticate($request);
    
    // if ($verifiedUser['status']['code'] != 200) {
    //     return $response->withStatus($verifiedUser['status']['code']);
    // }

    $data = $request->getParsedBody();
    if (empty($data['uid'])) {
        return $response->withStatus(400);
    }

    $uid = $data['uid'];
    $user_req = $request->withAttribute('id', $uid);
    $user_res = getUser($user_req, $response);

    //$statusCode = $user_res->getStatusCode();
    $statusCode = $user_res['status']['code'];
    if ($statusCode == 401) {
        return $response->withStatus(401);
    }

    // IF THE USER ALREADY EXIST THEN RETURN THE EXISTING USER
    // ELSE INSERT THE NEW USER AND RETURN IT
    if (sizeof($user_res['data']) > 0) {
        return $response->withJson($user_res['data']);
    }

    
    // INSERT THE NEW USER
    if (empty($data['username']) || empty($data['account_type'])) {
        return $response->withStatus(400);
    }

    $profilePictureUrl = "gs://findhandyman-f0b74.appspot.com/images/profilepictures/default-profile.png";
    if (!empty($data['profilePictureUrl'])) {
        $profilePictureUrl =$data['profilePictureUrl'];
    }

    $db = new DB();
    $sql = "INSERT INTO `users`(`uid`, `username`, `picture`, `type`) VALUES(?,?,?,?)";
    $db->exec($sql, [$uid, $data['username'], $profilePictureUrl, $data['account_type']]);

    $newUser = getUser($user_req, $response);
    return $response->withJson($newUser['data']);
    
});

//BODY: uid, username, bio, account_type, profilePictureUrl, 
//  addresses[{address, lat, lng}], services[{service_id, start_price, end_price}], 
//  availability[{day_name, start_time, end_time}]
$app->put('/user/{id}', function(Request $request, Response $response){
    $auth = new Authentication();
    $verifiedUser = $auth->authenticate($request);
    
    if ($verifiedUser['status']['code'] != 200) {
        return $response->withStatus($verifiedUser['status']['code']);
    }
    
    // Retrieve the uid from the url
    $uid = $request->getAttribute('id');

    // Get the request body
    $body = $request->getParsedBody();

    //UPDATE USER DATA IN THE DATABASE
    $db = new DB();

    // Update the username if it is in the body
    if (!empty($body['username'])) {
        if (trim($body['username']) != "") {
            $sql = "UPDATE users SET username=? WHERE uid=?";
            $db->exec($sql, [$body['username'], $uid]);
        }
    }

    // Update the bio if it is in the body
    if (!empty($body['bio'])) {
        if (trim($body['bio']) != "") {
            $sql = "UPDATE users SET bio=? WHERE uid=?";
            $db->exec($sql, [$body['bio'], $uid]);
        }
    }

    // Update the profilePictureUrl if it is in the body
    if (!empty($body['profilePictureUrl'])) {
        if (trim($body['profilePictureUrl']) != "") {
            $sql = "UPDATE users SET picture=? WHERE uid=?";
            $db->exec($sql, [$body['profilePictureUrl'], $uid]);
        }
    }

    // Update user's addresses if it is in the request body
    if (isset($body['addresses'])) {
        //first remove all addresses of the user
        $sql = "DELETE FROM user_addresses WHERE uid=?";
        $db->exec($sql, [$uid]);

        // Then insert the new addresses
        $addresses = $body['addresses'];
        $addresses_count = sizeof($addresses);
        if ($addresses_count > 0) {
            $sql = "INSERT INTO user_addresses (uid, address, lat, lng) VALUES" . " ";
            $sqlExtra = "(?,?,?,?),";
            $sql_data = [];
            
            for ($i=0; $i < $addresses_count; $i++) {
                if ($i == ($addresses_count-1)) {
                    $sqlExtra = "(?,?,?,?)";
                }
                $sql .= $sqlExtra;
                array_push($sql_data, $uid, $addresses[$i]['address'], $addresses[$i]['lat'], $addresses[$i]['lng']);
            }
            $db->exec($sql, $sql_data);
        }
    }

    // Update user's services if it is in the request body
    if (isset($body['services'])) {
        //first remove all services of the user
        $sql = "DELETE FROM handyman_services WHERE handyman_id=?";
        $db->exec($sql, [$uid]);

        // Then insert the new services
        $services = $body['services'];
        $services_count = sizeof($services);
        if ($services_count > 0) {
            $sql = "INSERT INTO handyman_services (handyman_id, service_id, start_price, end_price) VALUES" . " ";
            $sqlExtra = "(?,?,?,?),";
            $sql_data = [];
            
            for ($i=0; $i < $services_count; $i++) {
                if ($i == ($services_count-1)) {
                    $sqlExtra = "(?,?,?,?)";
                }
                $sql .= $sqlExtra;
                array_push($sql_data, $uid, $services[$i]['service_id'], $services[$i]['start_price'], $services[$i]['end_price']);
            }
            $db->exec($sql, $sql_data);
        }
    }

    // Update user's availability if it is in the request body
    if (isset($body['availability'])) {
        //first remove all availability of the user
        $sql = "DELETE FROM handyman_working_days_time WHERE handyman_id=?";
        $db->exec($sql, [$uid]);

        // Then insert the new availability
        $availability = $body['availability'];
        $availability_count = sizeof($availability);
        if ($availability_count > 0) {
            $sql = "INSERT INTO handyman_working_days_time (handyman_id, day_name, start_time, end_time) VALUES" . " ";
            $sqlExtra = "(?,?,?,?),";
            $sql_data = [];
            
            for ($i=0; $i < $availability_count; $i++) {
                if ($i == ($availability_count-1)) {
                    $sqlExtra = "(?,?,?,?)";
                }
                $sql .= $sqlExtra;
                array_push($sql_data, $uid, $availability[$i]['day_name'], $availability[$i]['start_time'], $availability[$i]['end_time']);
            }
            $db->exec($sql, $sql_data);
        }
    }

    return $response->withJson($test)->withStatus(200);
});

$app->get('/user/{rateeId}/rating', function(Request $request, Response $response){
    $auth = new Authentication();
    $verifiedUser = $auth->authenticate($request);
    
    if ($verifiedUser['status']['code'] != 200) {
        return $response->withStatus($verifiedUser['status']['code']);
    } 

    $rateeId = $request->getAttribute('rateeId');

    // Get the request body
    $body = $request->getQueryParams();

    // Number of reviews to fetch
    $offset = 0;
    $limit = 5;
    if (!empty($body['offset'])) {
        $offset = $body['offset'];
    }
    if (!empty($body['limit'])) {
        $limit = $body['limit'];
    }

    $db = new DB();

    //Fetch Reviews
    $user_reviews_sql = "SELECT	ur.job_id, ur.rater_id, u.username as rater_username, 
                                u.picture as rater_picture_url,
                                ur.rating, ur.review, ur.date
                            FROM	user_ratings ur, users u
                            WHERE	ur.uid=?
                            AND		ur.rater_id = u.uid
                            ORDER BY ur.date DESC
                            LIMIT   " . $offset . ", " . $limit;
    $reviews = $db->query($user_reviews_sql, [$rateeId]);
    $user_ratings[0]['reviews'] = $reviews['data'];

    if ($offset == 0) {
        //Fetch Overall rating
        $fetch_overall_rating_sql = 'SELECT	ROUND(AVG(rating), 1) as overall_rating
                                    FROM	user_ratings
                                    WHERE	uid=?';
        $overall_rating = $db->query($fetch_overall_rating_sql, [$rateeId])['data'][0]['overall_rating'];
        $user_ratings[0]['overall_rating'] = $overall_rating;

        //Fetch number of reviews
        $fetch_review_count_sql = 'SELECT	COUNT(*) as review_count
                                    FROM	user_ratings
                                    WHERE	uid=?';
        $overall_rating = $db->query($fetch_review_count_sql, [$rateeId])['data'][0]['review_count'];
        $user_ratings[0]['review_count'] = $overall_rating;

        //Get user account type
        $account_type_sql = "SELECT type FROM users WHERE uid=?";
        $account_type = $db->query($account_type_sql, [$rateeId])['data'][0]['type'];
        
        if ($account_type == 'handyman') {
            //Fetch user services rating
            $services_rating_sql = "SELECT      s.service_name, ROUND(AVG(ur.rating), 1) as rating
                                    FROM        user_ratings ur, jobs j, services s
                                    WHERE       ur.uid = ?
                                    AND         ur.job_id = j.job_id
                                    AND         j.service_id = s.service_id
                                    GROUP BY    s.service_id";
            $services_rating = $db->query($services_rating_sql, [$rateeId])['data'];
            $user_ratings[0]['services_rating'] = $services_rating;
        }
    }

    return $response->withJson($user_ratings);
});

//BODY: uid(ratee_id), job_id, rater_id, rating, review, date
$app->post('/user/{rateeId}/rating', function(Request $request, Response $response){
    // $auth = new Authentication();
    // $verifiedUser = $auth->authenticate($request);
    
    // if ($verifiedUser['status']['code'] != 200) {
    //     return $response->withStatus($verifiedUser['status']['code']);
    // } 
    
    $rateeId = $request->getAttribute('rateeId');

    // Get the request body
    $body = $request->getParsedBody();
    
    if (empty($body['job_id']) || empty($body['rater_id']) || 
        empty($body['rating']) || empty($body['review']) || empty($body['date'])){
        return $response->withStatus(400);
    }

    $db = new DB();
    $sql = "INSERT INTO user_ratings (uid, job_id, rater_id, rating, review, date)
            VALUES(?,?,?,?,?,?)";
    $sql_data = [$rateeId, $body['job_id'], $body['rater_id'], $body['rating'],
                    $body['review'], $body['date']];
    $sth = $db->exec($sql, $sql_data);
    return $response->withStatus($sth['status']['code']);
});


function getUser($request, $response) {

    $res['status']['code'] = 200;
    $res['data'] = [];

    // $auth = new Authentication();
    // $verifiedUser = $auth->authenticate($request);
    // if ($verifiedUser['status']['code'] != 200) {
    //     //return $response->withStatus($verifiedUser['status']['code']);
    //     $res['status']['code'] = $verifiedUser['status']['code'];
    //     return $res;
    // }

    $uid = $request->getAttribute('id');

     //Fetch user data
    $db = new DB();
    $fetch_user_sql =   "SELECT uid, username, picture as profilePictureUrl, bio, 
                            type as account_type 
                            FROM users 
                            WHERE uid=?";
    $user = $db->query($fetch_user_sql, array($uid));
    if (sizeof($user['data']) == 0) {
        //return $response->withJson($user['data'])->withStatus($user['status']['code']);
        $res['status']['code'] = $verifiedUser['status']['code'];
        $res['data'] = $user['data'];
        return $res;
    }
        
    //Fetch user addresses
    $fetch_user_addresses_sql = 'SELECT address, lat, lng 
                                FROM user_addresses 
                                WHERE uid=?';
    $user_addresses = $db->query($fetch_user_addresses_sql, array($uid))['data'];
    $user['data'][0]['addresses'] = $user_addresses;

    //If the user is a handyman the following code will be executed
    $account_type = $user['data'][0]['account_type'];
    if ($account_type == 'handyman') {
        //Fetch Handyman stripe acc number
        $stripe_acc_no_sql = "SELECT stripe_account_id from handymen_stripe_account WHERE handyman_id=?";
        $stripe_acc_no = $db->query($stripe_acc_no_sql, [$uid])['data'][0]['stripe_account_id'];
        $user['data'][0]['stripe_account_id'] = $stripe_acc_no;

        //Fetch user services
        $user_services_sql = "SELECT	s.service_id, s.service_name, hs.start_price, hs.end_price
                    FROM	handyman_services hs, services s
                    WHERE	hs.handyman_id=?
                    AND		hs.service_id = s.service_id";
        $user_services = $db->query($user_services_sql, array($uid))['data'];
        $user['data'][0]['services'] = $user_services;

        //Fetch user availability
        $user_availability_sql = "SELECT day_name, start_time, end_time 
                        FROM handyman_working_days_time 
                        WHERE handyman_id = ?";
        $user_availability = $db->query($user_availability_sql, array($uid))['data'];
        $user['data'][0]['availability'] = $user_availability;
    } // END IF

    //return $response->withJson($user['data']);
    $res['data'] = $user['data'];
    return $res;
}



?>