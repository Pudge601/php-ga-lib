<?php

namespace PW\GA;


class WeightedSelector implements WeightedSelectorInterface
{

    /**
     * @var Chromosome[]
     */
    protected $population;

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
    protected $maxWeightedValue;

    /**
     * @var int
     */
    protected $randMax;

    /**
     * @param Chromosome[] $population
     * @param float $weightingCoef
     * @returns $this
     */
    public function init($population, $weightingCoef)
    {
        $this->population       = $population;
        $this->weightingCoef    = $weightingCoef;
        $this->populationCount  = count($population);
        $this->maxWeightedValue = pow(1 + $weightingCoef, $this->populationCount);
        $this->randMax          = mt_getrandmax();
        return $this;
    }

    /**
     * @return Chromosome
     */
    public function getChromosome()
    {
        $weightedValue = (mt_rand() / $this->randMax) * $this->maxWeightedValue;
        $logValue = floor(log($weightedValue, 1 + $this->weightingCoef));
        $index = ($this->populationCount - $logValue) - 1;
        return $this->population[$index];
    }

}
