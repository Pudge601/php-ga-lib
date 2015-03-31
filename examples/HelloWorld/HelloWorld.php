<?php

namespace PW\GA\Example\HelloWorld;

use PW\GA\ChromosomeGenerator;
use PW\GA\Config;
use PW\GA\CrossoverMethod;
use PW\GA\FitnessCalculatorInterface;
use PW\GA\GeneticAlgorithm;
use PW\GA\MutateMethod;
use PW\GA\Example\LoggerError;
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
     * @return \mixed[]
     */
    public function findSolution($options)
    {
        $options = array_merge([
            Config::SORT_DIR => GeneticAlgorithm::SORT_DIR_DESC,
        ], $options);

        $gaEngine = new GeneticAlgorithm(
            $this,
            new ChromosomeGenerator\Word($this->alphabet, strlen($this->target)),
            new CrossoverMethod\OnePointCrossover(),
            new MutateMethod\ModifyWord($this->alphabet),
            new Config($options)
        );

        $gaEngine->setSuccessCriteria($this)
            ->setLogger(new LoggerError());

        $solution = $gaEngine->findSolution();

        return $solution;
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
     * @param array $value
     * @return bool
     */
    public function validateSuccess(array $value)
    {
        return implode('', $value) === $this->target;
    }

}
