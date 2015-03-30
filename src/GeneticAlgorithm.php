<?php

namespace PW\GA;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use PW\GA\ChromosomeGenerator\ChromosomeGeneratorInterface;
use PW\GA\CrossoverMethod\CrossoverMethodInterface;
use PW\GA\MutateMethod\MutateMethodInterface;

class GeneticAlgorithm implements LoggerAwareInterface
{
    const MAX_ALLOWED_POPULATION = 50000;

    const SORT_TYPE_ASC  = 0;
    const SORT_TYPE_DESC = 1;

    /**
     * @var FitnessCalculatorInterface
     */
    protected $fitnessCalculator;

    /**
     * @var ChromosomeGeneratorInterface
     */
    protected $chromosomeGenerator;

    /**
     * @var CrossoverMethodInterface
     */
    protected $crossoverMethod;

    /**
     * @var MutateMethodInterface
     */
    protected $mutateMethod;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Chromosome[]
     */
    protected $population = [];

    /**
     * @var float
     */
    protected $entropy = 0.5;

    /**
     * @var int
     */
    protected $populationCount = 50;

    /**
     * @var int
     */
    protected $logFrequency = 10;

    /**
     * @var int
     */
    protected $sortDir = GeneticAlgorithm::SORT_TYPE_DESC;

    /**
     * @param FitnessCalculatorInterface $fitnessCalculator
     * @param ChromosomeGeneratorInterface $chromosomeGenerator
     * @param CrossoverMethodInterface $crossoverMethod
     * @param MutateMethodInterface $mutateMethod
     */
    public function __construct(
        FitnessCalculatorInterface $fitnessCalculator,
        ChromosomeGeneratorInterface $chromosomeGenerator,
        CrossoverMethodInterface $crossoverMethod,
        MutateMethodInterface $mutateMethod
    ) {
        $this->fitnessCalculator   = $fitnessCalculator;
        $this->chromosomeGenerator = $chromosomeGenerator;
        $this->crossoverMethod     = $crossoverMethod;
        $this->mutateMethod        = $mutateMethod;
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param int $logFrequency
     * @return $this
     */
    public function setLogFrequency($logFrequency)
    {
        $this->logFrequency = $logFrequency;
        return $this;
    }

    /**
     * @param float $entropy
     * @return $this
     */
    public function setEntropy($entropy)
    {
        if ($entropy < 0 || $entropy > 1) {
            throw new InvalidArgumentException('Entropy must be a float between 0 and 1');
        }
        $this->entropy = $entropy;
        return $this;
    }

    /**
     * @param int $populationCount
     * @return $this
     */
    public function setPopulationCount($populationCount)
    {
        if ($populationCount < 0 || $populationCount > self::MAX_ALLOWED_POPULATION) {
            throw new InvalidArgumentException('Population count must be between 0 and ' . self::MAX_ALLOWED_POPULATION);
        }
        $this->populationCount = $populationCount;
        return $this;
    }

    /**
     * @param int $sortDir
     * @return $this
     */
    public function setSortDir($sortDir)
    {
        $this->sortDir = $sortDir;
        return $this;
    }

    /**
     * @param ChromosomeGeneratorInterface $chromosomeGenerator
     * @return $this
     */
    public function setChromosomeGenerator(ChromosomeGeneratorInterface $chromosomeGenerator)
    {
        $this->chromosomeGenerator = $chromosomeGenerator;
        return $this;
    }

    /**
     * @param CrossoverMethodInterface $crossoverMethod
     * @return $this
     */
    public function setCrossoverMethod(CrossoverMethodInterface $crossoverMethod)
    {
        $this->crossoverMethod = $crossoverMethod;
        return $this;
    }

    /**
     * @param MutateMethodInterface $mutateMethod
     * @return $this
     */
    public function setMutateMethod(MutateMethodInterface $mutateMethod)
    {
        $this->mutateMethod = $mutateMethod;
        return $this;
    }

    /**
     * Search for a solution, returning the best chromosome at the end
     *
     * @param int $maxIterations
     * @return mixed[]
     */
    public function findSolution($maxIterations)
    {
        $this->initPopulation();

        $this->sortPopulation();

        for ($i = 0; $i < $maxIterations; $i++) {
            $stats = $this->runIteration();
            if ($i % $this->logFrequency === 0) {
                $this->logStatus($i, $stats);
            }
        }

        return $this->population[0]->getValue();
    }

    /**
     * @return $this
     */
    public function initPopulation()
    {
        $chromosomes = $this->chromosomeGenerator->generateChromosomes($this->populationCount);

        foreach ($chromosomes as $chromosome) {
            $this->addChromosome($chromosome);
        }

        return $this;
    }

    /**
     * Performs a single iteration
     * Assumes that the chromosomes are already sorted
     *
     * @return array
     */
    protected function runIteration()
    {
        $noCulled  = $this->cull();

        $noBred    = $this->crossover();

        $noMutated = $this->mutate();

        $this->sortPopulation();

        return [
            'culled'  => $noCulled,
            'bred'    => $noBred,
            'mutated' => $noMutated,
        ];
    }

    /**
     * Sort the population of chromosomes by fitness
     */
    protected function sortPopulation()
    {
        usort($this->population, function(Chromosome $chromosomeA, Chromosome $chromosomeB) {
            $fitnessA = $chromosomeA->getFitness($this->fitnessCalculator);
            $fitnessB = $chromosomeB->getFitness($this->fitnessCalculator);
            if ($fitnessA == $fitnessB) {
                return 0;
            }
            return $this->sortDir === GeneticAlgorithm::SORT_TYPE_ASC
                ? (($fitnessA < $fitnessB) ? -1 : 1)
                : (($fitnessA < $fitnessB) ? 1 : -1)
                ;
        });
    }

    /**
     * Cull the weaker chromosomes from the population
     */
    protected function cull()
    {
        $cullCount = floor($this->entropy * (count($this->population) * 0.5));
        for ($i = 0; $i < $cullCount; $i++) {
            array_pop($this->population);
        }
        return $cullCount;
    }

    /**
     * Crossover the stronger chromosomes to increase the population
     */
    protected function crossover()
    {
        $populationCount = count($this->population);
        $breedCount = floor($this->entropy * ($populationCount * 0.7));
        $breedCount = min($breedCount, floor(($this->populationCount - $populationCount) / 2));

        $maxWeightedValue = 1 << ($populationCount - 1);
        for ($i = 0; $i < $breedCount; $i++) {
            /* @var Chromosome[] $breedPartners */
            $breedPartners = [];
            for ($j = 0; $j < 2; $j++) {
                $weightedValue = rand(1, $maxWeightedValue);
                $logValue = floor(log($weightedValue, 2));
                $index = ($populationCount - $logValue) - 1;
                $breedPartners[] = $this->population[$index];
            }

            $newValue = $this->crossoverMethod->crossover(
                $breedPartners[0]->getValue(),
                $breedPartners[1]->getValue()
            );
            $this->addChromosome(new Chromosome($newValue));
        }
        return $breedCount;
    }

    /**
     * Get mutations of the stronger chromosomes
     */
    protected function mutate()
    {
        $populationCount = count($this->population);
        $mutateCount = floor($this->entropy * ($populationCount * 0.7));
        $mutateCount = min($mutateCount, $this->populationCount - $populationCount);

        $maxWeightedValue = 1 << ($populationCount - 1);
        for ($i = 0; $i < $mutateCount; $i++) {
            $weightedValue = rand(1, $maxWeightedValue);
            $logValue = floor(log($weightedValue, 2));
            $index = ($populationCount - $logValue) - 1;
            $mutateChromosome = $this->population[$index];

            $newValue = $this->mutateMethod->mutate($mutateChromosome->getValue(), $this->entropy);
            $this->addChromosome(new Chromosome($newValue));
        }
        return $mutateCount;
    }

    /**
     * @param Chromosome $chromosome
     * @return $this
     */
    protected function addChromosome(Chromosome $chromosome)
    {
        $this->population[] = $chromosome;
        return $this;
    }

    /**
     * Log the current status of the population
     *
     * @param int $iterationNumber
     * @param array $stats
     */
    protected function logStatus($iterationNumber, $stats)
    {
        $fitnessTotal = 0;
        /* @var Chromosome $chromosome */
        foreach ($this->population as $chromosome) {
            $fitnessTotal += $chromosome->getFitness($this->fitnessCalculator);
        }
        $populationCount = count($this->population);
        $averageFitness  = $fitnessTotal / $populationCount;
        $bestFitness     = $this->population[0]->getFitness($this->fitnessCalculator);

        $this->log(
            "Iteration #$iterationNumber: " .
            "Population: $populationCount, " .
            "Culled: {$stats['culled']}, " .
            "Bred: {$stats['bred']}, " .
            "Mutated: {$stats['mutated']}, " .
            "Best fitness: $bestFitness, " .
            "Average fitness: $averageFitness"
        );
    }

    /**
     * @param string $message
     */
    protected function log($message)
    {
        if (isset($this->logger)) {
            $this->logger->info($message);
        }
    }
}
