<?php
/**
 * Created by PhpStorm.
 * User: stevejunior
 * Date: 13/04/2020
 * Time: 6:21 AM
 */

require_once('headers.php');
require ('helper.php');
include '../Impact.php';
include '../SevereImpact.php';

// get posted data
$start   = microtime(true);
$request = json_decode(file_get_contents("php://input"), true);

if(gettype($request) !== "array"){
    $request  = (array) $request;
}

$impact       = new Impact($request);
$severeImpact = new SevereImpact($request);

$response = responseOutput($request, $impact->getPayload(), $severeImpact->getPayload());
http_response_code(200);

echo json_encode($response);
$end = microtime(true);

logActivity($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], 200, $end - $start);