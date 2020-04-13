<?php
include 'Impact.php';
include 'SevereImpact.php';

// $request = array(
//   "region" => [
//     "name" => "Africa",
//     "avgAge"=> 19.7,
//     "avgDailyIncomeInUSD"=> 3,
//     "avgDailyIncomePopulation"=> 0.72
//   ],
//   "periodType"=> "days",
//   "timeToElapse"=> 6,
//   "reportedCases"=> 2115,
//   "population"=> 9247213,
//   "totalHospitalBeds"=> 62715
// );

// covid19ImpactEstimator($request);


//Entry point
function covid19ImpactEstimator($data)
{
  $impact = new Impact($data);
  $severeImpact = new SevereImpact($data);

  $response = responseOutput($data, $impact->getPayload(), $severeImpact->getPayload());
  print_r($response);
  return $response;
}


function responseOutput($request, $impact, $severeImpact){
  return ['data' => $request, 'impact' => $impact, 'severeImpact' => $severeImpact];
}