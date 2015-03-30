<?php

namespace PW\GA;

class Config
{

    const ENTROPY          = 'entropy';
    const POPULATION_COUNT = 'populationCount';
    const LOG_FREQUENCY    = 'logFrequency';
    const SORT_DIR         = 'sortDir';
    const MAX_ITERATIONS   = 'maxIterations';

    /**
     * @var array
     */
    protected $data = [
        self::ENTROPY          => 0,5,
        self::POPULATION_COUNT => 50,
        self::LOG_FREQUENCY    => 10,
        self::SORT_DIR         => GeneticAlgorithm::SORT_DIR_DESC,
        self::MAX_ITERATIONS   => 5000
    ];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value)
    {
        if (!isset($this->data[$key])) {
            throw new InvalidArgumentException("Invalid config key '$key'");
        }
        $this->data[$key] = $this->filter($key, $value);
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->data[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function filter($key, $value)
    {
        $filterMethod = 'filter' . ucfirst($key);
        if (method_exists($this, $filterMethod)) {
            $value = $this->{$filterMethod}($value);
        }
        return $value;
    }

    /**
     * @param float $entropy
     * @return float
     */
    public function filterEntropy($entropy)
    {
        if ($entropy < 0 || $entropy > 1) {
            throw new InvalidArgumentException('Entropy must be a float between 0 and 1');
        }
        return $entropy;
    }

    /**
     * @param int $populationCount
     * @return int
     */
    public function filterPopulationCount($populationCount)
    {
        if ($populationCount < 0 || $populationCount > GeneticAlgorithm::MAX_ALLOWED_POPULATION) {
            throw new InvalidArgumentException(
                'Population count must be between 0 and ' . GeneticAlgorithm::MAX_ALLOWED_POPULATION
            );
        }
        return $populationCount;
    }

    /**
     * @param int $sortDir
     * @return int
     */
    public function filterSortDir($sortDir)
    {
        if ($sortDir !== GeneticAlgorithm::SORT_DIR_ASC &&
            $sortDir !== GeneticAlgorithm::SORT_DIR_DESC
        ) {
            throw new InvalidArgumentException(
                'Sort direction must be either \'' . GeneticAlgorithm::SORT_DIR_ASC
                . '\' or \'' . GeneticAlgorithm::SORT_DIR_DESC . '\''
            );
        }
        return $sortDir;
    }

}