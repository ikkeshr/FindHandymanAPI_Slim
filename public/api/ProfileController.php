<?php

//header("Access-Control-Allow-Origin: *");
require_once ("Profile.php");

 // Getting the received JSON into $json variable.
$json = file_get_contents('php://input');
	 
// decoding the received JSON and store into $obj variable.
$obj = json_decode($json,true);

// Populate product name from JSON $obj array and store into $product_name.
$action = $obj['action'];

$profile = new Profile();

switch ( $action ) {

    case "profileDetails":
        echo $profile->fetchProfileDetails($obj['data']);
    break;

    case "profileInfo":
        echo $profile->fetchProfileInfo($obj['data']);
    break;

    case "profileServicesRating":
        echo $profile->fetchProfileServicesRating($obj['data']);
    break;

    case "profileReviews":
        echo $profile->fetchProfileReviews($obj['data']);
    break;
	
	case "fetchAddresses":
		echo $profile->fetchUserAddresses($obj['data']);
	break;
	
	case "fetchUserWorkingInfo":
		echo $profile->fetchUserWorkingInfo($obj['data']);
	break;
	
	case "profileAvailability":
		echo $profile->fetchProfileAvailability($obj['data']);
	break;
	
	case "getAccountType":
		$sql = "SELECT type from users WHERE uid=?";
		$db = new DBController();
		echo $db->query2($sql, array($obj['data']['uid']));
	break;

    default:
		echo json_encode($obj);
	break;
}

?>