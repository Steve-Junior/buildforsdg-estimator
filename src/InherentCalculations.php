<?php
/**
 * Created by PhpStorm.
 * User: stevejunior
 * Date: 12/04/2020
 * Time: 11:34 PM
 */

class InherentCalculations
{
    private $periodType;
    private $timeFrame;
    private $reportedCases;
    private $totalHospitalBeds;
    private $avgDailyIncome;
    private $avgIncomeInUsd;

    private $currentlyInfected;
    private $infectionsOverTime;
    private $severeInfectionsOverTime;


    public function __construct($data)
    {
        $this->periodType = $data['periodType'];
        $this->timeFrame  = $data['timeToElapse'];
        $this->reportedCases = $data['reportedCases'];
        $this->totalHospitalBeds = $data['totalHospitalBeds'];
        $this->avgDailyIncome = $data['region']['avgDailyIncomePopulation'];
        $this->avgIncomeInUsd = $data['region']['avgDailyIncomeInUSD'];
    }

    private function normaliseDuration(){
        switch ($this->periodType){
            case "months":
                return $this->timeFrame * 30;
            case "weeks":
                return $this->timeFrame * 7;
            case "days":
            default:
                return $this->timeFrame;
        }
    }

    public function getCurrentlyInfected($factor){
        $this->currentlyInfected = intval($this->reportedCases * $factor);
        return $this->currentlyInfected;
    }

    public function getInfectedByRequestedTime(){
        $period = $this->normaliseDuration();
        $factor = intval($period/3);
        $this->infectionsOverTime = $this->currentlyInfected * pow(2, $factor);

        return $this->infectionsOverTime;
    }

    public function getSeverePositiveCasesOverTime(){
        $this->severeInfectionsOverTime = 0.15 * $this->infectionsOverTime;
        return $this->severeInfectionsOverTime;
    }

    public function getAvailableHospitalBeds(){

        $estimateAvailableBeds = 0.35 * $this->totalHospitalBeds;
        $result = $estimateAvailableBeds - $this->severeInfectionsOverTime;

        return intval($result);
    }

    public function getCasesForIcuOverTime(){
        return intval(0.05 * $this->infectionsOverTime);
    }

    public function getCasesForVentilatorsOverTime(){
        return intval(0.02 * $this->infectionsOverTime);
    }

    public function getDollarsInFlight(){
        $cases = $this->infectionsOverTime;
        $period = $this->normaliseDuration();
        $estimatedLost = ($cases * $this->avgDailyIncome * $this->avgIncomeInUsd)/$period;
        return intval($estimatedLost);
    }
}