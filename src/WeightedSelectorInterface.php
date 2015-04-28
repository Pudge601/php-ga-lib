<?php

namespace PW\GA;


interface WeightedSelectorInterface
{

    /**
     * @param $populationCount
     * @param $weightingCoef
     * @return $this
     */
    public function init($populationCount, $weightingCoef);

    /**
     * @return int
     */
    public function nextIndex();

}
