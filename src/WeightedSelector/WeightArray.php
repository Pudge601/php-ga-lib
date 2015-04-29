<?php

namespace PW\GA\WeightedSelector;

use PW\GA\WeightedSelectorInterface;

class WeightArray implements WeightedSelectorInterface
{

    /**
     * @var int
     */
    protected $populationCount;

    /**
     * @var array
     */
    protected $weights;

    /**
     * @var float
     */
    protected $minWeightedValue = 0;

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
        $this->populationCount  = $populationCount;

        $this->buildWeights(1 + $weightingCoef);

        return $this;
    }

    /**
     * Gets the next random index between 0 and (populationCount - 1)
     *
     * Uses weighting to prefer the lower indices, with the magnitude of the weighting based on the value of
     * the weightingCoef, such that in descending order
     * each index is (1 + weightingCoef) times MORE like to be chosen than the last
     *
     * Therefore, a weightingCoef of 0 will give every index equal probability of being selected (no weighting)
     *
     * @return int
     */
    public function nextIndex()
    {
        $weightedValue = mt_rand($this->minWeightedValue, $this->maxWeightedValue);
        $index = $this->findIndex($weightedValue);
        return ($this->populationCount - $index) - 1;
    }

    /**
     * Uses a binary search to find the index with the corresponding weight
     *
     * @param int $weightedValue
     * @return int
     */
    protected function findIndex($weightedValue)
    {
        $min = 0;
        $max = count($this->weights) - 1;
        $mid = 0;
        while ($min < $max) {
            $mid = (int)ceil(($min + $max) / 2);
            if ($this->weights[$mid] < $weightedValue) {
                $min = $mid + 1;
            } else if ($this->weights[$mid] > $weightedValue) {
                $max = $mid - 1;
            } else {
                return $mid;
            }
        }
        if ($min != $max) {
            return $this->weights[$min] >= $weightedValue ? $min : $mid;
        } else {
            return $this->weights[$min] >= $weightedValue ? $min : $min + 1;
        }
    }

    /**
     * @param float $weightingCoef
     */
    protected function buildWeights($weightingCoef)
    {
        $this->weights = [];
        $lastWeight = 0;
        $diff       = 1;
        for ($i = 0; $i < $this->populationCount; $i++) {
            $this->weights[$i] = $lastWeight + $diff;
            $diff      *= $weightingCoef;
            $lastWeight = $this->weights[$i];
        }
        $this->maxWeightedValue = $lastWeight;
    }

}
