<?php

//header("Access-Control-Allow-Origin: *");
require_once ("DBController.php");

 // Getting the received JSON into $json variable.
$json = file_get_contents('php://input');
	 
// decoding the received JSON and store into $obj variable.
$obj = json_decode($json,true);

// Populate product name from JSON $obj array and store into $product_name.
$action = $obj['action'];
$data = $obj['data'];

$db = new DBController();

switch ( $action ) {

    case "get_other_user":
        //uid, username, profilePictureUrl
        $viewer_id = $data['uid'];
        $job_id = $data['job_id'];

        $sql = "SELECT j.job_giver as client_id, js.handyman_id 
                FROM jobs j, job_status js 
                WHERE j.job_id=?
                AND j.job_id = js.job_id";

        $ids = json_decode($db->query2($sql, array($job_id)), true)[0];
        //print_r($ids);

        //if viewer is the handyman then fetch client details and vice versa
        //id, name, photoUrl
        if ($ids['handyman_id'] == $viewer_id) {
            $sql = "SELECT	uid as id, username as name, picture as profilePictureUrl
                    FROM	users
                    WHERE 	uid=?";
            echo $db->query2($sql, array($ids['client_id']));
        }
        else if ($ids['client_id'] == $viewer_id) {
            $sql = "SELECT	uid as id, username as name, picture as profilePictureUrl
                    FROM	users
                    WHERE 	uid=?";
            echo $db->query2($sql, array($ids['handyman_id']));
        }
        
    break;

    case "get_this_user":
        //uid, username, profilePictureUrl
        $sql = "SELECT  uid, username, picture as profilePictureUrl
                FROM    users
                WHERE   uid=?";
        echo $db->query2($sql, array($data['uid']));
    break;

    default:
		echo json_encode($obj);
	break;
}