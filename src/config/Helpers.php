<?php

function getResponseStruct() {
    $res['status']['code'] = 200;
    $res['status']['msg'] = "";
    $res['status']['rawMsg'] = "";
    $res['data'] = [];
    return $res;
}

// Get the value of 1 USD in MUR
function USD_MUR() {
    $session = curl_init('https://free.currconv.com/api/v7/convert?q=USD_MUR&compact=ultra&apiKey=ff88e49b3067aa8c317e');
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    $usd_mur = json_decode(curl_exec($session), true)['USD_MUR'];
    return $usd_mur;
}

?>