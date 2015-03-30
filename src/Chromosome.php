<?php

namespace PW\GA;


class Chromosome
{
    /**
     * @var float
     */
    private $fitness;

    /**
     * @var mixed[]
     */
    private $value;

    /**
     * @constructor
     * @param mixed[] $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed[]
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param FitnessCalculatorInterface $fitnessCalculator
     * @return float|int
     */
    public function getFitness(FitnessCalculatorInterface $fitnessCalculator)
    {
        if (!isset($this->fitness)) {
            $this->fitness = $fitnessCalculator->calculateFitness($this->getValue());
        }
        return $this->fitness;
    }

}
