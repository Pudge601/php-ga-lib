<?php

namespace PW\GA\Example\HelloWorld;

use PW\GA\Chromosome;
use PW\GA\ChromosomeGenerator;
use PW\GA\Config;
use PW\GA\CrossoverMethod;
use PW\GA\FitnessCalculatorInterface;
use PW\GA\GeneticAlgorithm;
use PW\GA\MutateMethod;
use PW\GA\SuccessCriteriaInterface;

class HelloWorld implements FitnessCalculatorInterface, SuccessCriteriaInterface
{

    /**
     * @var string
     */
    protected $target;

    /**
     * @var array
     */
    protected $alphabet;

    /**
     * @param string $target
     * @param array $alphabet
     */
    public function __construct($target = 'Hello World', $alphabet = null)
    {
        $this->alphabet = $alphabet ?: range('A', 'z');
        $this->target = $target;

        // ensure all characters in 'target' are in 'alphabet'
        $this->alphabet = array_unique(array_merge($this->alphabet, str_split($this->target)));
    }

    /**
     * @param array $options
     * @param int $maxIterations
     * @return \mixed[]
     */
    public function findSolution($options, $maxIterations)
    {
        $options = array_merge([
            Config::SORT_DIR => GeneticAlgorithm::SORT_DIR_DESC,
        ], $options);

        $gaEngine = new GeneticAlgorithm(
            $this,
            new CrossoverMethod\TwoPointCrossover(),
            new MutateMethod\ModifyWord($this->alphabet),
            new Config($options)
        );

        $gaEngine->initPopulation(new ChromosomeGenerator\Word($this->alphabet, strlen($this->target)))
            ->optimiseUntil($this, $maxIterations);

        return $gaEngine->getFittest()->getValue();
    }

    /**
     * @param array $value
     * @return float
     */
    public function calculateFitness(array $value)
    {
        $stringValue = implode('', $value);
        $target      = $this->target;
        similar_text($stringValue, $target, $percent);
        return $percent / 100;
    }

    /**
     * @param Chromosome $fittest
     * @return bool
     */
    public function validateSuccess(Chromosome $fittest)
    {
        return implode('', $fittest->getValue()) === $this->target;
    }

}
