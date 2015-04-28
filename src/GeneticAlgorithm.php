<?php

namespace PW\GA;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class GeneticAlgorithm implements LoggerAwareInterface
{
    const MAX_ALLOWED_POPULATION = 50000;

    const SORT_DIR_ASC  = 0;
    const SORT_DIR_DESC = 1;

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
     * @var WeightedSelectorInterface
     */
    protected $weightedSelector;

    /**
     * @var SuccessCriteriaInterface
     */
    protected $successCriteria;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Chromosome[]
     */
    protected $population = [];

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param FitnessCalculatorInterface $fitnessCalculator
     * @param ChromosomeGeneratorInterface $chromosomeGenerator
     * @param CrossoverMethodInterface $crossoverMethod
     * @param MutateMethodInterface $mutateMethod
     * @param Config $config
     */
    public function __construct(
        FitnessCalculatorInterface $fitnessCalculator,
        ChromosomeGeneratorInterface $chromosomeGenerator,
        CrossoverMethodInterface $crossoverMethod,
        MutateMethodInterface $mutateMethod,
        Config $config = null
    ) {
        $this->fitnessCalculator   = $fitnessCalculator;
        $this->chromosomeGenerator = $chromosomeGenerator;
        $this->crossoverMethod     = $crossoverMethod;
        $this->mutateMethod        = $mutateMethod;
        $this->config              = $config ?: new Config();

        $this->weightedSelector    = new WeightedSelector();
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
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
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
     * @param WeightedSelectorInterface $weightedSelector
     * @return $this
     */
    public function setWeightedSelector(WeightedSelectorInterface $weightedSelector)
    {
        $this->weightedSelector = $weightedSelector;
        return $this;
    }

    /**
     * @param SuccessCriteriaInterface $successCriteria
     * @return $this
     */
    public function setSuccessCriteria(SuccessCriteriaInterface $successCriteria)
    {
        $this->successCriteria = $successCriteria;
        return $this;
    }

    /**
     * Search for a solution, returning the best chromosome at the end
     *
     * @return mixed[]
     */
    public function findSolution()
    {
        $this->initPopulation();

        $this->sortPopulation();

        $maxIterations = $this->config->get(Config::MAX_ITERATIONS);
        $logFrequency  = $this->config->get(Config::LOG_FREQUENCY);
        for ($i = 0; $i < $maxIterations; $i++) {
            $stats = $this->runIteration();

            if ($this->validateSuccess()) {
                $this->log('Success criteria achieved');
                break;
            }

            if ($i % $logFrequency === 0) {
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
        $populationCount = $this->config->get(Config::POPULATION_COUNT);
        $chromosomes     = $this->chromosomeGenerator->generateChromosomes($populationCount);

        foreach ($chromosomes as $chromosome) {
            foreach ($chromosome->getValue() as $gene) {
                if (!is_scalar($gene)) {
                    throw new UnexpectedValueException('All chromosome\'s genes must be scalar type');
                }
            }
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
        list($cullCount, $crossoverCount, $mutateCount) = $this->calculateChurn();

        $this->cull($cullCount);

        $this->crossover($crossoverCount);

        $this->mutate($mutateCount);

        $this->sortPopulation();

        return [
            'culls'      => $cullCount,
            'crossovers' => $crossoverCount,
            'mutations'  => $mutateCount,
        ];
    }

    /**
     * Sort the population of chromosomes by fitness
     */
    protected function sortPopulation()
    {
        $sortDir = $this->config->get(Config::SORT_DIR);
        usort($this->population, function(Chromosome $chromosomeA, Chromosome $chromosomeB) use ($sortDir) {
            $fitnessA = $chromosomeA->getFitness($this->fitnessCalculator);
            $fitnessB = $chromosomeB->getFitness($this->fitnessCalculator);
            if ($fitnessA == $fitnessB) {
                return 0;
            }
            return $sortDir === GeneticAlgorithm::SORT_DIR_ASC
                ? (($fitnessA < $fitnessB) ? -1 : 1)
                : (($fitnessA < $fitnessB) ? 1 : -1)
                ;
        });
    }

    /**
     * Cull the weaker chromosomes from the population
     *
     * @param int $cullCount
     */
    protected function cull($cullCount)
    {
        for ($i = 0; $i < $cullCount; $i++) {
            array_pop($this->population);
        }
    }

    /**
     * Crossover the stronger chromosomes to increase the population
     *
     * @param int $crossoverCount
     */
    protected function crossover($crossoverCount)
    {
        $this->weightedSelector->init(count($this->population), $this->config->get(Config::WEIGHTING_COEF));
        for ($i = 0; $i < $crossoverCount;) {
            /* @var Chromosome[] $breedPartners */
            $breedPartners = [];
            for ($j = 0; $j < 2; $j++) {
                $index = $this->weightedSelector->nextIndex();
                $breedPartners[] = $this->population[$index];
            }

            $offspring = $this->crossoverMethod->crossover(
                $breedPartners[0]->getValue(),
                $breedPartners[1]->getValue()
            );
            foreach ($offspring as $childValue) {
                $this->addChromosome(new Chromosome($childValue));
                $i++;
                if ($i === $crossoverCount) {
                    break;
                }
            }
        }
    }

    /**
     * Get mutations of the stronger chromosomes
     *
     * @param int $mutateCount
     */
    protected function mutate($mutateCount)
    {
        $mutateEntropy = $this->config->get(Config::MUTATE_ENTROPY);
        $this->weightedSelector->init(count($this->population), $this->config->get(Config::WEIGHTING_COEF));
        for ($i = 0; $i < $mutateCount; $i++) {
            $index = $this->weightedSelector->nextIndex();
            $mutateChromosome = $this->population[$index];

            $newValue = $this->mutateMethod->mutate($mutateChromosome->getValue(), $mutateEntropy);
            $this->addChromosome(new Chromosome($newValue));
        }
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
     * Calculates how many chromosomes to cull, how many should be created through
     * crossover, and how many should be created by mutation
     *
     * @return array
     */
    protected function calculateChurn()
    {
        $churnEntropy    = $this->config->get(Config::CHURN_ENTROPY);
        $populationCount = count($this->population);

        $cullCount = floor($churnEntropy * ($populationCount * 0.5));
        $remaining = $populationCount - $cullCount;
        $available = $this->config->get(Config::POPULATION_COUNT) - $remaining;

        $crossoverCount = floor($available / 2);
        $mutateCount    = $available - $crossoverCount;

        return [$cullCount, $crossoverCount, $mutateCount];
    }

    /**
     * @return bool
     */
    protected function validateSuccess()
    {
        if (isset($this->successCriteria)) {
            $bestSolution = $this->population[0];
            return $this->successCriteria->validateSuccess($bestSolution->getValue());
        }
        return false;
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
            "Culls: {$stats['culls']}, " .
            "Crossovers: {$stats['crossovers']}, " .
            "Mutations: {$stats['mutations']}, " .
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
