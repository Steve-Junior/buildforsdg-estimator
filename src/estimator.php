<?php

 $request = array(
   "region" => [
     "name" => "Africa",
     "avgAge"=> 19.7,
     "avgDailyIncomeInUSD"=> 3,
     "avgDailyIncomePopulation"=> 0.72
   ],
   "periodType"=> "days",
   "timeToElapse"=> 6,
   "reportedCases"=> 2115,
   "population"=> 9247213,
   "totalHospitalBeds"=> 62715
 );

 covid19ImpactEstimator($request);

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
    $estimateBedAvailable = 0.35 * $totalHospitalBeds;
    $result = $estimateBedAvailable - $severeCasesOverTime;

    return intval($result);
}

function getCasesForICUByRequestedTime($casesByRequestedTime){
  $result = 0.05 * $casesByRequestedTime;
  return intval($result);
}

function getCasesForVentilatorsByRequestedTime($casesByRequestedTime){
  $result =  0.02 * $casesByRequestedTime;
  return intval($result);
}

function getDollarsInFlight($cases, $region, $type, $period){
  $period = normaliseDuration($period, $type);

  $estimatedLost = ($cases * $region['avgDailyIncomePopulation'] * $region['avgDailyIncomeInUSD']) / $period;

  return intval($estimatedLost);
}

//Entry point
function covid19ImpactEstimator($data)
{
  ['population' => $population, 'region' => $region, 'periodType' => $periodType, 'timeToElapse' => $period,  'reportedCases' => $cases,  'totalHospitalBeds' => $totalHospitalBeds] = $data;
 
  $impact = [];
  $severeImpact = [];

  //Challenge 1
  $impact['currentlyInfected'] = getCurrentlyInfected($cases, 10);
  $severeImpact['currentlyInfected'] = getCurrentlyInfected($cases, 50);

  $impactInfectionOverTime  = getInfectedByRequestedTime($impact['currentlyInfected'], $period, $periodType);
  $sevImpactInfectionOverTime = getInfectedByRequestedTime($severeImpact['currentlyInfected'], $period, $periodType);
  $impact['infectionsByRequestedTime'] = round($impactInfectionOverTime, 1);
  $severeImpact['infectionsByRequestedTime'] = round($sevImpactInfectionOverTime, 1);

  //challenge 2
  $impactSevereCasesOverTime  = getSeverePositiveCases($impactInfectionOverTime);
  $sevImpactSevereCasesOverTime  = getSeverePositiveCases($sevImpactInfectionOverTime);

  $impact['severeCasesByRequestedTime'] = number_format($impactSevereCasesOverTime, 1, '.', '');
  $severeImpact['severeCasesByRequestedTime'] = number_format($sevImpactSevereCasesOverTime, 1, '.', '');

  
  $impact['hospitalBedsByRequestedTime'] = getAvailableHospitalBeds($impactSevereCasesOverTime, $totalHospitalBeds);
  $severeImpact['hospitalBedsByRequestedTime'] = getAvailableHospitalBeds($sevImpactSevereCasesOverTime, $totalHospitalBeds);


  //Challenge 3
  $impact['casesForICUByRequestedTime'] = getCasesForICUByRequestedTime($impactInfectionOverTime);
  $severeImpact['casesForICUByRequestedTime'] = getCasesForICUByRequestedTime($sevImpactInfectionOverTime);

  $impact['casesForVentilatorsByRequestedTime'] = getCasesForVentilatorsByRequestedTime($impactInfectionOverTime);
  $severeImpact['casesForVentilatorsByRequestedTime'] = getCasesForVentilatorsByRequestedTime($sevImpactInfectionOverTime);

  $impact['dollarsInFlight'] = getDollarsInFlight($population, $region, $periodType, $period);
  $severeImpact['dollarsInFlight'] = getDollarsInFlight($population, $region, $periodType, $period);

  $response = responseOutput($data, $impact, $severeImpact);
  print_r($response['impact']);
  return $response;
}


function responseOutput($request, $impact, $severeImpact){
  return ['data' => $request, 'impact' => $impact, 'severeImpact' => $severeImpact];
}