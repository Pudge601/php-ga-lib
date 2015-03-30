<?php

namespace PW\GA;

interface FitnessCalculatorInterface
{

    /**
     * @param mixed[] $value
     * @return float|int
     */
    public function calculateFitness(array $value);

}
