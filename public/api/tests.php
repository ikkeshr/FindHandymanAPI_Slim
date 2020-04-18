<?php

/* require_once ("Model.php");
$model = new Model();
$data['job_id'] = 35;
$data['status'] = 'ongoing';
$data
echo $model->update_job($data); */

require_once ('Services.php');
require_once ('DBController.php');
$service = new Services();

$data['limit'] = 5;
$data['offset'] = 0;
$data['language'] = 'fr';

//echo $service->get($data);
$db = new DBController();
echo $db->query("SELECT service_description_fr FROM services");

?>