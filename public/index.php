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

$app->get('/transactions', function(Request $request, Response $response){
    \Stripe\Stripe::setApiKey('sk_test_8iCp41fdeuKUWnnkr6mnYY0j00MHIRxbhA');

    
    $applicationFee = \Stripe\ApplicationFee::retrieve(
        'fee_1GYwjPEFqTJjlMqzkKY2ACPF'
    );

    // $chargeId = $applicationFee['charge'];

    // $charge = \Stripe\Charge::retrieve(
    //     $chargeId
    // );

    return $response->withJson($applicationFee);
});

// routes
require '../src/routes/users.php';
require '../src/routes/services.php';
require '../src/routes/jobs.php';
require '../src/routes/payments.php';

// Run app
$app->run();

?>