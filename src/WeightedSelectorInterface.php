<?php

namespace PW\GA;


interface WeightedSelectorInterface
{

    /**
     * @param $population
     * @param $weightingCoef
     * @return $this
     */
    public function init($population, $weightingCoef);

    /**
     * @return Chromosome
     */
    public function getChromosome();

}
