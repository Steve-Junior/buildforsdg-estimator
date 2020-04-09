<?php

// $request = array(
//   "region" => [
//     "name" => "Africa",
//     "avgAge"=> 19.7,
//     "avgDailyIncomeInUSD"=> 5,
//     "avgDailyIncomePopulation"=> 0.71
//   ],
//   "periodType"=> "days",
//   "timeToElapse"=> 58,
//   "reportedCases"=> 674,
//   "population"=> 66622705,
//   "totalHospitalBeds"=> 1380614
// );

// covid19ImpactEstimator($request);

function getCurrentlyInfected($reportedCases, $factor) : int{
  return intval($reportedCases) * $factor;
}

function getInfectedByRequestedTime($cases, $period, $periodType){
  $period = normaliseDuration($period, $periodType);

  $factor = intval($period/3);

  return $cases * pow(2, $factor);
}


function normaliseDuration($period, $type) : int{
  if($type === "days") return $period;

  else if($type === "weeks") return $period * 7;

  else if ($type === "months") return $period * 30;

  else throw new Exception('Period type must be in days');
}

function getSeverePositiveCases($cases){
  return 0.15 * intval($cases);
}

function getAvailableHospitalBeds($severeCasesOverTime, $totalHospitalBeds){
  // $estimateBedAvailable = 0.35 * intval($totalHospitalBeds);
  return $totalHospitalBeds - $severeCasesOverTime;
}

function getCasesForICUByRequestedTime($casesByRequestedTime){
  return 0.05 * intval($casesByRequestedTime);
}

function getCasesForVentilatorsByRequestedTime($casesByRequestedTime){
  return 0.02 * intval($casesByRequestedTime);
}

function getDollarsInFlight($cases, $region, $type, $period){
  $period = normaliseDuration($period, $type);

  $estimatedLost = $cases * $region['avgDailyIncomePopulation'] * $region['avgDailyIncomeInUSD'] * $period;

  return number_format($estimatedLost, 2); 
}

//Entry point
function covid19ImpactEstimator($data)
{
  ['region' => $region, 'periodType' => $periodType, 'timeToElapse' => $period,  'reportedCases' => $cases,  'totalHospitalBeds' => $totalHospitalBeds] = $data;
 
  $impact = [];
  $severeImpact = [];

  //Challenge 1
  $impact['currentlyInfected'] = getCurrentlyInfected($cases, 10);
  $severeImpact['currentlyInfected'] = getCurrentlyInfected($cases, 50);

  $impact['infectionsByRequestedTime'] = getInfectedByRequestedTime($impact['currentlyInfected'], $period, $periodType);
  $severeImpact['infectionsByRequestedTime'] = getInfectedByRequestedTime($severeImpact['currentlyInfected'], $period, $periodType);

  //challenge 2
  $impact['severeCasesByRequestedTime'] = getSeverePositiveCases($impact['infectionsByRequestedTime']);
  $severeImpact['severeCasesByRequestedTime'] = getSeverePositiveCases($severeImpact['infectionsByRequestedTime']);

  $impact['hospitalBedsByRequestedTime'] = getAvailableHospitalBeds($impact['severeCasesByRequestedTime'], $totalHospitalBeds);
  $severeImpact['hospitalBedsByRequestedTime'] = getAvailableHospitalBeds($severeImpact['severeCasesByRequestedTime'], $totalHospitalBeds);


  //Challenge 3
  $impact['casesForICUByRequestedTime'] = getCasesForICUByRequestedTime($impact['infectionsByRequestedTime']);
  $severeImpact['casesForICUByRequestedTime'] = getCasesForICUByRequestedTime($severeImpact['infectionsByRequestedTime']);

  $impact['casesForVentilatorsByRequestedTime'] = getCasesForVentilatorsByRequestedTime($impact['infectionsByRequestedTime']);
  $severeImpact['casesForVentilatorsByRequestedTime'] = getCasesForVentilatorsByRequestedTime($severeImpact['infectionsByRequestedTime']);

  $impact['dollarsInFlight'] = getDollarsInFlight($impact['infectionsByRequestedTime'], $region, $periodType, $period);
  $severeImpact['dollarsInFlight'] = getDollarsInFlight($severeImpact['infectionsByRequestedTime'], $region, $periodType, $period);

  $response = responseOutput($data, $impact, $severeImpact);
  // echo gettype($response);
  return $response;
}


function responseOutput($request, $impact, $severeImpact){
  return ['data' => $request, 'impact' => $impact, 'severeImpact' => $severeImpact];
}