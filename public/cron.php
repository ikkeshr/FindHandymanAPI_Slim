<?php
// CRON JOB TO NOTIFY USERS HAVING A JOB ON THE CURRENT DATE:
// + retrieve job where date is the current date
// + insert record in firestore, notifType="reminder"

require_once "../src/config/DB.php";

$db = new DB();

date_default_timezone_set('Indian/Mauritius');
$currentDate = date('Y-m-d');

//client_id, handyman_id, job_id, type, ISODate
$sql = "SELECT  j.job_giver as client_id, js.handyman_id, j.job_id
        FROM    jobs j, job_status js
        WHERE   j.date=?
        AND     js.status=?
        AND     j.job_id=js.job_id";

$jobData = $db->query($sql, [$currentDate, 'ongoing'])['data'];

if (sizeof($jobData) > 0) {
    $type="reminder";
    $job_id = $jobData[0]['job_id'];
    $date=date('D M d Y H:i:s O');
    $viewed = false;
    $client_id = $jobData[0]['client_id'];
    $handyman_id = $jobData[0]['handyman_id'];

    echo $type . " " . $date . " " . $client_id . " " . $handyman_id;

    // + insert record in firestore for client and handyman
}

?>