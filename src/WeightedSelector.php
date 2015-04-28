<?php

namespace PW\GA;


class WeightedSelector implements WeightedSelectorInterface
{

    /**
     * @var float
     */
    protected $weightingCoef;

    /**
     * @var int
     */
    protected $populationCount;

    /**
     * @var float
     */
    protected $minWeightedValue = 1;

    /**
     * @var float
     */
    protected $maxWeightedValue;

    /**
     * @param int $populationCount
     * @param float $weightingCoef
     * @returns $this
     */
    public function init($populationCount, $weightingCoef)
    {
        $this->weightingCoef    = $weightingCoef;
        $this->populationCount  = $populationCount;
        $this->maxWeightedValue = pow(1 + $weightingCoef, $this->populationCount);
        return $this;
    }

    /**
     * @return int
     */
    public function nextIndex()
    {
        $weightedValue = $this->getRandomFloat($this->minWeightedValue, $this->maxWeightedValue);
        $logValue = floor(log($weightedValue, 1 + $this->weightingCoef));
        return ($this->populationCount - $logValue) - 1;
    }

    /**
     * @param float $min
     * @param float $max
     * @return float
     */
    protected function getRandomFloat($min, $max)
    {
        return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
    }

}
