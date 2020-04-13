<?php
/**
 * Created by PhpStorm.
 * User: stevejunior
 * Date: 13/04/2020
 * Time: 6:22 AM
 */
require_once('headers.php');

$logs = file_get_contents('activity.log');

echo $logs;