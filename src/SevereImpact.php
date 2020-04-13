<?php
/**
 * Created by PhpStorm.
 * User: stevejunior
 * Date: 12/04/2020
 * Time: 11:32 PM
 */


class SevereImpact extends InherentCalculations
{
    private static $FACTOR = 50;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getPayload(){
        // full severe impact objects
        return [
            'currentlyInfected'          => $this->getCurrentlyInfected(self::$FACTOR),
            'infectionsByRequestedTime'  => $this->getInfectedByRequestedTime(),
            'severeCasesByRequestedTime' => $this->getSeverePositiveCasesOverTime(),
            'hospitalBedsByRequestedTime'=> $this->getAvailableHospitalBeds(),
            'casesForICUByRequestedTime' => $this->getCasesForIcuOverTime(),
            'casesForVentilatorsByRequestedTime' => $this->getCasesForVentilatorsOverTime(),
            'dollarsInFlight'            => $this->getDollarsInFlight()
        ];
    }
}