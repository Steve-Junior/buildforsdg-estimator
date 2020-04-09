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

function getDollarsInFlight($cases, $request){
  $period = normaliseDuration($request->timeToElapse, $request->periodType);
  $region = $request->region;

  $estimateLost = $cases * $region->avgDailyIncomePopulation * $region->avgDailyIncomeInUSD * $period;

  return number_format($estimateLost, 2); 
}

//Entry point
function covid19ImpactEstimator($request)
{
  $data = json_decode(json_encode($request, FALSE));

  $impact = new StdClass;
  $severeImpact = new StdClass;

  //Challenge 1
  $impact->currentlyInfected = getCurrentlyInfected($data->reportedCases, 10);
  $severeImpact->currentlyInfected = getCurrentlyInfected($data->reportedCases, 50);

  $impact->infectionsByRequestedTime = getInfectedByRequestedTime($impact->currentlyInfected, $data->timeToElapse, $data->periodType);
  $severeImpact->infectionsByRequestedTime = getInfectedByRequestedTime($severeImpact->currentlyInfected, $data->timeToElapse, $data->periodType);


  //challenge 2
  $impact->severeCasesByRequestedTime = getSeverePositiveCases($impact->infectionsByRequestedTime);
  $severeImpact->severeCasesByRequestedTime = getSeverePositiveCases($severeImpact->infectionsByRequestedTime);

  $impact->hospitalBedsByRequestedTime = getAvailableHospitalBeds($impact->severeCasesByRequestedTime, $data->totalHospitalBeds);
  $severeImpact->hospitalBedsByRequestedTime = getAvailableHospitalBeds($severeImpact->severeCasesByRequestedTime, $data->totalHospitalBeds);


  //Challenge 3
  $impact->casesForICUByRequestedTime = getCasesForICUByRequestedTime($impact->infectionsByRequestedTime);
  $severeImpact->casesForICUByRequestedTime = getCasesForICUByRequestedTime($severeImpact->infectionsByRequestedTime);

  $impact->casesForVentilatorsByRequestedTime = getCasesForVentilatorsByRequestedTime($impact->infectionsByRequestedTime);
  $severeImpact->casesForVentilatorsByRequestedTime = getCasesForVentilatorsByRequestedTime($severeImpact->infectionsByRequestedTime);

  $impact->dollarsInFlight = getDollarsInFlight($impact->infectionsByRequestedTime, $data);
  $severeImpact->dollarsInFlight = getDollarsInFlight($severeImpact->infectionsByRequestedTime, $data);

  $response = responseOutput($data, $impact, $severeImpact);
  echo gettype($response);
  return $response;
}


function responseOutput($request, $impact, $severeImpact){
  return ['data' => $request, 'impact' => $impact, 'severeImpact' => $severeImpact];
}