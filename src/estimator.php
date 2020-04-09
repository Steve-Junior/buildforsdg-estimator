<?php

// $request = array(
//   "region" => [
//     "name" => "Africa",
//     "avgAge"=> 19.7,
//     "avgDailyIncomeInUSD"=> 6,
//     "avgDailyIncomePopulation"=> 0.59
//   ],
//   "periodType"=> "days",
//   "timeToElapse"=> 97,
//   "reportedCases"=> 1573,
//   "population"=> 174894727,
//   "totalHospitalBeds"=> 2826583
// );

// covid19ImpactEstimator($request);

function getCurrentlyInfected($reportedCases, $factor){
  return $reportedCases * $factor;
}

function getInfectedByRequestedTime($cases, $time, $type){
  $period = normaliseDuration($time, $type);

  $factor = intval($period/3);

  return $cases * pow(2, $factor);
}


function normaliseDuration($period, $type){
  if($type === "days") return $period;

  else if($type === "weeks") return $period * 7;

  else if ($type === "months") return $period * 30;

  else return $period;
}

function getSeverePositiveCases($cases){
  return 0.15 * $cases;
}

function getAvailableHospitalBeds($severeCasesOverTime, $totalHospitalBeds){
  // $estimateBedAvailable = 0.35 * intval($totalHospitalBeds);
  return $totalHospitalBeds - $severeCasesOverTime;
}

function getCasesForICUByRequestedTime($casesByRequestedTime){
  return 0.05 * $casesByRequestedTime;
}

function getCasesForVentilatorsByRequestedTime($casesByRequestedTime){
  return 0.02 * $casesByRequestedTime;
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

  $impactInfectionOverTime  = getInfectedByRequestedTime($impact['currentlyInfected'], $period, $periodType);
  $sevImpactInfectionOverTime = getInfectedByRequestedTime($severeImpact['currentlyInfected'], $period, $periodType);
  $impact['infectionsByRequestedTime'] = number_format($impactInfectionOverTime, 1, '.', '');
  $severeImpact['infectionsByRequestedTime'] = number_format($sevImpactInfectionOverTime, 1, '.', '');

  //challenge 2
  $impactSevereCasesOverTime  = getSeverePositiveCases($impactInfectionOverTime);
  $sevImpactSevereCasesOverTime  = getSeverePositiveCases($sevImpactInfectionOverTime);

  $impact['severeCasesByRequestedTime'] = number_format($impactSevereCasesOverTime, 1, '.', '');
  $severeImpact['severeCasesByRequestedTime'] = number_format($sevImpactSevereCasesOverTime, 1, '.', '');

  
  $impact['hospitalBedsByRequestedTime'] = number_format(getAvailableHospitalBeds($impactSevereCasesOverTime, $totalHospitalBeds), 1, '.', '');
  $severeImpact['hospitalBedsByRequestedTime'] = number_format(getAvailableHospitalBeds($sevImpactSevereCasesOverTime, $totalHospitalBeds), 1, '.', '');


  //Challenge 3
  $impact['casesForICUByRequestedTime'] = number_format(getCasesForICUByRequestedTime($impactInfectionOverTime), 1, '.', '');
  $severeImpact['casesForICUByRequestedTime'] = number_format(getCasesForICUByRequestedTime($sevImpactInfectionOverTime), 1, '.', '');

  $impact['casesForVentilatorsByRequestedTime'] = number_format(getCasesForVentilatorsByRequestedTime($impactInfectionOverTime), 1, '.', '');
  $severeImpact['casesForVentilatorsByRequestedTime'] = number_format(getCasesForVentilatorsByRequestedTime($sevImpactInfectionOverTime),1, '.', '');

  $impact['dollarsInFlight'] = getDollarsInFlight($impactInfectionOverTime, $region, $periodType, $period);
  $severeImpact['dollarsInFlight'] = getDollarsInFlight($sevImpactInfectionOverTime, $region, $periodType, $period);

  $response = responseOutput($data, $impact, $severeImpact);
  print_r($response);
  return $response;
}


function responseOutput($request, $impact, $severeImpact){
  return ['data' => $request, 'impact' => $impact, 'severeImpact' => $severeImpact];
}