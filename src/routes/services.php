<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app = new \Slim\App;


$app->get('/services', function(Request $request, Response $response){
    //AUTHENTICATION
    $auth = new Authentication();
    $verifiedUser = $auth->authenticate($request);
    if ($verifiedUser['status']['code'] != 200) {
        return $response->withStatus($verifiedUser['status']['code']);
    }

    $offset = 0;
    $limit = 5;

    //$body = $request->getParsedBody();
    $body = $request->getQueryParams();

    if (!empty($body['offset'])) {
        $offset = $body['offset'];
    }
    if (!empty($body['limit'])) {
        $limit = $body['limit'];
    }

    $lang = "en";

    if (!empty($body['lang'])) {
        $lang = $body['lang'];
    }

    $db = new DB();

    if ($lang == "en") {
        $sql = "SELECT  service_id, service_name_en as service_name, service_description_en as service_description
                FROM    services
                LIMIT   " . $offset . ", " . $limit;
    } else {
        $sql = "SELECT  service_id, service_name_fr as service_name, service_description_fr as service_description
                FROM    services
                LIMIT   " . $offset . ", " . $limit;
    }

    $services = $db->query($sql);

    return $response->withJson($services['data'])->withStatus($services['status']['code']);

});


$app->get('/services/{handymanId}', function(Request $request, Response $response){
    //AUTHENTICATION
    $auth = new Authentication();
    $verifiedUser = $auth->authenticate($request);
    if ($verifiedUser['status']['code'] != 200) {
        return $response->withStatus($verifiedUser['status']['code']);
    }

    $db = new Db();

    $handymanId = $request->getAttribute('handymanId');
    $params = $request->getQueryParams();

    $lang = "en";
    if (!empty($params['lang'])) {
        $lang = $params['lang'];
    }

    $sql = "SELECT service_id FROM handyman_services WHERE handyman_id=?";
    $user_services = array_column($db->query($sql, [$handymanId])['data'], "service_id");
    $excludeUserExistingServicesSQLString = "";

    if (sizeof($user_services) > 0) {
        $excludeUserExistingServicesSQLString = "WHERE service_id NOT IN (".implode(',',$user_services).")";
    }

    $sql = "";
    if ($lang == "en") {
        $sql = "SELECT service_id, service_name_en as service_name FROM services " . $excludeUserExistingServicesSQLString;
    } else if ($lang == "fr") {
        $sql = "SELECT service_id, service_name_fr as service_name FROM services " . $excludeUserExistingServicesSQLString;
    }

    $userNotServices = $db->query($sql);

    return $response->withJson($userNotServices['data'])->withStatus($userNotServices['status']['code']);
});

?>