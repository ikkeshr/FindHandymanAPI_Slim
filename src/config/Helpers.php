<?php

function getResponseStruct() {
    $res['status']['code'] = 200;
    $res['status']['msg'] = "";
    $res['status']['rawMsg'] = "";
    $res['data'] = [];
    return $res;
}

?>