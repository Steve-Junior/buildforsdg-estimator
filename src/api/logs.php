<?php
/**
 * Created by PhpStorm.
 * User: stevejunior
 * Date: 13/04/2020
 * Time: 6:22 AM
 */
require_once('headers.php');
require ('helper.php');

$start   = microtime(true);
$response = file_get_contents('activity.log');

header("Content-type: text/plain");
http_response_code(200);

echo $response;

$end = microtime(true);
logActivity($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], 200, $end - $start);