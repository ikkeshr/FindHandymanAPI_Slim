<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require "../vendor/autoload.php";
require "../src/config/DB.php";
require "../src/config/Authentication.php";
require "../src/config/Helpers.php";
require "../src/config/Storage.php"; // To download pictures from firebase storage


// Create and configure Slim app
$config = ['settings' => [
    'addContentLengthHeader' => false,
    'displayErrorDetails' => true,
]];
$app = new \Slim\App($config);



// routes
require '../src/routes/users.php';
require '../src/routes/services.php';
require '../src/routes/jobs.php';
require '../src/routes/payments.php';

// Run app
$app->run();

?>