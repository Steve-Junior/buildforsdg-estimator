<?php
/**
 * Created by PhpStorm.
 * User: stevejunior
 * Date: 12/04/2020
 * Time: 11:31 PM
 */


include 'InherentCalculations.php';



class Impact extends InherentCalculations
{
    private static $FACTOR = 10;

    public function __construct($data)
    {
       parent::__construct($data);
    }

    public function getPayload(){
        // full impact objects
        return [
            'currentlyInfected'         => $this->getCurrentlyInfected(self::$FACTOR),
            'infectionsByRequestedTime' => $this->getInfectedByRequestedTime(),
            'severeCasesByRequestedTime'=> $this->getSeverePositiveCasesOverTime(),
            'hospitalBedsByRequestedTime'=> $this->getAvailableHospitalBeds(),
            'casesForICUByRequestedTime' => $this->getCasesForIcuOverTime(),
            'casesForVentilatorsByRequestedTime' => $this->getCasesForVentilatorsOverTime(),
            'dollarsInFlight'           => $this->getDollarsInFlight()
        ];
    }
}