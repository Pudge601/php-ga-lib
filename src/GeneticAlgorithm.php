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
     */
    protected function cull()
    {
        $entropy   = $this->config->get(Config::ENTROPY);
        $cullCount = floor($entropy * (count($this->population) * 0.5));
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
        $maxPopulation   = $this->config->get(Config::POPULATION_COUNT);
        $populationCount = count($this->population);
        $entropy    = $this->config->get(Config::ENTROPY);
        $breedCount = floor($entropy * ($populationCount * 0.7));
        $breedCount = min($breedCount, floor(($maxPopulation - $populationCount) / 2));

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
        $maxPopulation   = $this->config->get(Config::POPULATION_COUNT);
        $populationCount = count($this->population);
        $entropy = $this->config->get(Config::ENTROPY);
        $mutateCount = floor($entropy * ($populationCount * 0.7));
        $mutateCount = min($mutateCount, $maxPopulation - $populationCount);

        $maxWeightedValue = 1 << ($populationCount - 1);
        for ($i = 0; $i < $mutateCount; $i++) {
            $weightedValue = rand(1, $maxWeightedValue);
            $logValue = floor(log($weightedValue, 2));
            $index = ($populationCount - $logValue) - 1;
            $mutateChromosome = $this->population[$index];

            $newValue = $this->mutateMethod->mutate($mutateChromosome->getValue(), $entropy);
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
