<?php

/* use Kreait\Firebase\Factory;
use Firebase\Auth\Token\Exception\InvalidToken;
require "../vendor/autoload.php";


$factory = (new Factory)->withServiceAccount('../src/config/findhandyman-f0b74-4d16b7fd9ffb.json');
$auth = $factory->createAuth();

$signInResult = $auth->signInWithEmailAndPassword('client1@email.com', '123456');

echo $signInResult->idToken(); */

// require "../src/config/DB.php";
// $db = new Db();

// $body['addresses'][0]['address'] = 'Grand Bois';
// $body['addresses'][0]['lat'] = -20.4188774;
// $body['addresses'][0]['lng'] = 57.5511401;

// $body['addresses'][1]['address'] = 'Grand Bay';
// $body['addresses'][1]['lat'] = -20.4188774;
// $body['addresses'][1]['lng'] = 57.5511401;

// $body['addresses'][2]['address'] = 'Grand Bay';
// $body['addresses'][2]['lat'] = -20.4188774;
// $body['addresses'][2]['lng'] = 57.5511401;

// $uid = "xxxxxxxx";

// if (isset($body['addresses'])) {
//     //first remove all addresses of the user
//     $sql = "DELETE FROM user_addresses WHERE uid=?";
//     //$this->db->exec($sql, [$uid]);

//     // Then insert the new addresses
//     $sql = "INSERT INTO user_addresses (uid, address, lat, lng) VALUES" . " ";
//     $sqlExtra = "(?,?,?,?),";
//     $sql_data = [];

//     $addresses = $body['addresses'];
//     $addresses_count = sizeof($addresses);
//     for ($i=0; $i < $addresses_count; $i++) {
//         if ($i == ($addresses_count-1)) {
//             $sqlExtra = "(?,?,?,?)";
//         }
//         $sql .= $sqlExtra;
//         array_push($sql_data, $uid, $addresses[$i]['address'], $addresses[$i]['lat'], $addresses[$i]['lng']);
//     }
//     echo $sql . "</br>";
//     echo json_encode($sql_data);
//}

// require "../src/config/DB.php";
// $db = new Db();
// $uid = "nwBfWEivmoTyZGfAywX6cEAg34g1";

// $sql = "SELECT service_id FROM handyman_services WHERE handyman_id=?";
// $user_services = array_column($db->query($sql, [$uid])['data'], "service_id");
// echo json_encode($user_services);

// $excludeUserExistingServicesSQLString = "";

// if (sizeof($user_services) > 0) {
//     $excludeUserExistingServicesSQLString = "WHERE service_id NOT IN (".implode(',',$user_services).")";
// }

// $lang = "en";
// $sql = "";
// if ($lang == "en") {
//     $sql = "SELECT service_id, service_name_en as service_name FROM services " . $excludeUserExistingServicesSQLString;
// } else if ($lang == "fr") {
//     $sql = "SELECT service_id, service_name_fr as service_name FROM services " . $excludeUserExistingServicesSQLString;
// }

// //$userNotServices = $db->query($sql)['data'];

// echo $sql;

// require "../vendor/autoload.php";
// use Google\Cloud\Storage\StorageClient;

// $storage = new StorageClient([
//     'keyFilePath' => '../src/config/findhandyman-f0b74-4d16b7fd9ffb.json'
// ]);

// $bucket = $storage->bucket("findhandyman-f0b74.appspot.com");

// // Download and store an object from the bucket locally.
// $object = $bucket->object('images/profilepictures/default-profile.png');
// //$object->downloadToFile('/data/default-profile.png')
// $d = new DateTime('tomorrow');
// echo $object->signedUrl($d);
//print_r($d);

phpinfo();
?>