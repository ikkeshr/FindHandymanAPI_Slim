<?php

//header("Access-Control-Allow-Origin: *");
require_once ("DBController.php");

 // Getting the received JSON into $json variable.
$json = file_get_contents('php://input');
	 
// decoding the received JSON and store into $obj variable.
$obj = json_decode($json,true);

// Populate product name from JSON $obj array and store into $product_name.
$action = $obj['action'];

$db = new DBController();

switch ( $action ) {
	case "create":
		$sql = "INSERT INTO users(uid, username, type) VALUES (?,?,?)";
		$db->exec2($sql, array($obj['uid'], $obj['username'], $obj['type']) );
		break;

	case "create_user":
		require_once ('Authentication.php');
		$auth = new Authentication();
		echo $auth->create_user( $obj['data'] );
	break;

	case "getAccountType":
		$sql = "SELECT type from users WHERE uid=?";
		echo $db->query2($sql, array($obj['uid']));
		break;

	case "getServices":
		require_once ('Services.php');
		$service = new Services();
		echo $service->getServices($obj['data']);
		break;
	
	case "getAllServicesTypes":
		$sql = "SELECT service_id, service_name from services";
		echo $db->query($sql);
		break;
	
	case "getServiceDesc":
		$sql = "SELECT service_description FROM services WHERE service_id=?";
		echo $db->query2( $sql, array($obj['service_id']) );
		break;
		
	case "getOtherServices":
		require_once ('Services.php');
		$service = new Services();
		echo $service->getOtherServices($obj['data']);
	break;
		
	case "registerClient":
		require_once ('Authentication.php');
		$auth = new Authentication();
		$auth->registerClient( $obj['data'] );
		break;
	
	case "registerHandyman":
		require_once ('Authentication.php');
		$auth = new Authentication();
		$auth->registerHandyman( $obj['data'] );
		break;

	case "getUserAddresses":
		$sql = "SELECT address, lat, lng FROM user_addresses WHERE uid=?";
		echo $db->query2( $sql, array($obj['uid']) );
		break;
		
	case "insertJob":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->insertJob($obj['data']);
	break;

	case "deleteJob":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->deleteJob($obj['data']);
	break;
	
	
	case "insertJobPictures":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		$jobs->insertJobPictures($obj['data']);
	break;
	
	case "fetchHandymen":
		require_once ('Match.php');
		$match = new Match();
		echo $match->fetchHandyman();
		break;
		
	case "matchHandyman":
		require_once ('Match.php');
		$match = new Match();
		echo $match->matchHandyman2($obj['data']);
		break;
	
	case "fetchMyJobs": 
		require_once ('Jobs.php');
		$jobs = new Jobs();
		//data: userId, accountType, segment
		echo $jobs->fetchMyJobs($obj['data']);
		break;
		
	case "fetchJobDetails":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->fetchJobDetails($obj['data']);
	break;

	case "actionButtonData":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->actionButtonData($obj['data']);
	break;

	case "editProfile":
		require_once ('Authentication.php');
		$auth = new Authentication();
		$auth->editProfile( $obj['data'] );
	break;

	case "fetchReviewPageData":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->fetchReviewPageData($obj['data']);
	break;
	
	case "postReview":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->postReview($obj['data']);
	break;

	case "getNotificationData":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->getNotificationData($obj['data']);
	break;

	case "fetch_handyman_contact":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->fetch_handyman_contact($obj['data']);
	break;

	case "fetch_client_contact":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->fetch_client_contact($obj['data']);
	break;

	//Notifications...
	case "bookHandyman":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->bookHandyman($obj['data']);
	break;

	case "cancelBooking":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->cancelBooking($obj['data']);
	break;

	case "cancelJob":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->cancelJob($obj['data']);
	break;

	case "nofitUpdate":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->nofitUpdate($obj['data']);
	break;

	case "reschedule_job":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->reschedule_job($obj['data']);
	break;

	case "accept_job_reschedule":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->accept_reschedule($obj['data']);
	break;

	case "refuse_job_reschedule":
		require_once ('Jobs.php');
		$jobs = new Jobs();
		echo $jobs->refuse_reschedule($obj['data']);
	break;
	
	default:
		echo json_encode($obj);
	break;

}


?>