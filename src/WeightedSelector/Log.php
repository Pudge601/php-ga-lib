<?php

namespace PW\GA\WeightedSelector;

use PW\GA\WeightedSelectorInterface;

class Log implements WeightedSelectorInterface
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
     * Gets the next random index between 0 and (populationCount - 1)
     *
     * Uses weighting to prefer the lower indexes, with the magnitude of the weighting based on the value of
     * the weightingCoef
     *
     * Picks a random float between 1 and (1 + weightingCoef) ^ populationCount, and returns the index at
     * populationCount - log(randomValue, 1 + weightinCoef) - 1
     *
     * E.g. for a weightinCoef of 1, each descending index should be twice as likely to be picked as the previous
     *
     * **NOTE**
     * This method does not work with a weightingCoef of 0, since (1 + 0) ^ n = 1
     *
     * To give every index the same probability of being selected (equal weighting),
     * use \PW\GA\WeightedSelector\WeightArray with weightingCoef of 0 to give every index equal weighting
     *
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
