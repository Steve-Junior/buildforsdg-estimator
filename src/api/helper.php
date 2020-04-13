<?php
/**
 * Created by PhpStorm.
 * User: stevejunior
 * Date: 13/04/2020
 * Time: 6:25 AM
 */

if(! function_exists('logActivity')){
    function logActivity($requestMethod, $path, $httpStatus, $responseTimeInMicroSec, $request = []){
        $resTimeInMs = round($responseTimeInMicroSec * 1000, 4);
        $data = $requestMethod."\t".$path."\t".$httpStatus."\t".$resTimeInMs."ms";

        $log_file    = "activity.log";
        $file_stream = fopen($log_file, 'a');

        fwrite($file_stream, $data.PHP_EOL);
        if(sizeof($request) > 0){
            fwrite($file_stream, $request.PHP_EOL);
        }

        fclose($file_stream);
    }
}

if(! function_exists('responseOutput')){
    function responseOutput($request, $impact, $severeImpact){
        return ['data' => $request, 'impact' => $impact, 'severeImpact' => $severeImpact];
    }
}

if(! function_exists('array_to_xml')){
    function array_to_xml( $array, $rootElement = null, $xml = null ) {
        $_xml = $xml;

        // If there is no Root Element then insert root
        if ($_xml === null) {
            $_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<root/>');
        }

        // Visit all key value pair
        foreach ($array as $k => $v) {

            // If there is nested array then
            if (is_array($v)) {

                // Call function for nested array
                array_to_xml($v, $k, $_xml->addChild($k));
            }

            else {

                // Simply add child element.
                $_xml->addChild($k, $v);
            }
        }

        return $_xml->asXML();
    }
}

