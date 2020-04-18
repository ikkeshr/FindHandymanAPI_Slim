<?php

//header("Access-Control-Allow-Origin: *");
require_once ("Model.php");

 // Getting the received JSON into $json variable.
$json = file_get_contents('php://input');
	 
// decoding the received JSON and store into $obj variable.
$obj = json_decode($json,true);

// Populate product name from JSON $obj array and store into $product_name.
$action = $obj['action'];

$model = new Model();

switch ( $action ) {

    case "get_user":
        echo $model->get_user($obj['data']);
    break;

    case "create_user":
        echo $model->create_user($obj['data']);
    break;

    case "remove_user":
        echo $model->remove_user($obj['data']);
    break;

    case "update_user":
        echo $model->update_user($obj['data']);
    break;

    case "get_services":
        echo $model->get_services($obj['data']);
    break;

    case "get_user_ratings":
        echo $model->get_user_ratings($obj['data']);
    break;

    case "create_review":
        echo $model->create_review($obj['data']);
    break;

    case "get_job":
        echo $model->get_job($obj['data']);
    break;

    case "create_job":
        echo $model->create_job($obj['data']);
    break;

    case "remove_job":
        echo $model->remove_job($obj['data']);
    break;

    case "update_job":
        echo $model->update_job($obj['data']);
    break;

    case "get_current_job":
        echo $model->get_current_jobs($obj['data']);
    break;

    case "get_past_job":
        echo $model->get_past_jobs($obj['data']);
    break;


    default:
		echo json_encode($obj);
	break;
    
}

?>