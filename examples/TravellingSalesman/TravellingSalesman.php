<?php

namespace PW\GA\Example\TravellingSalesman;

use PW\GA\ChromosomeGenerator;
use PW\GA\Config;
use PW\GA\CrossoverMethod;
use PW\GA\Example\LoggerError;
use PW\GA\FitnessCalculatorInterface;
use PW\GA\GeneticAlgorithm;
use PW\GA\MutateMethod;

class TravellingSalesman implements FitnessCalculatorInterface
{

    /**
     * @var Vector[]
     */
    protected $cities;

    /**
     * @param int $numberOfCities
     * @param int $worldSize
     * @return Vector[]
     */
    public static function createRandomCities($numberOfCities, $worldSize = 100)
    {
        $cities = [];
        for ($i = 0; $i < $numberOfCities; $i++) {
            $x = mt_rand(0, $worldSize);
            $y = mt_rand(0, $worldSize);
            $cities[] = new Vector($x, $y);
        }
        return $cities;
    }

    /**
     * @param $array
     * @return $this
     */
    public function setCities($array)
    {
        $this->cities = [];
        foreach ($array as $coords) {
            $this->cities[] = new Vector($coords[0], $coords[1]);
        }
        return $this;
    }

    /**
     * @return Vector[]
     */
    public function getCities()
    {
        if (!isset($this->cities)) {
            $this->cities = self::createRandomCities(20, 600);
        }
        return $this->cities;
    }

    /**
     * @param array $options
     * @return \mixed[]
     */
    public function findSolution($options)
    {
        $options = array_merge([
            Config::SORT_DIR => GeneticAlgorithm::SORT_DIR_ASC,
        ], $options);

        $gaEngine = new GeneticAlgorithm(
            $this,
            new ChromosomeGenerator\OrderedList(range(0, count($this->cities) - 1)),
//            new CrossoverMethod\OrderedList\EdgeRecombination(),
            new CrossoverMethod\OrderedList\OrderCrossover(),
            new MutateMethod\GeneSwap(),
            new Config($options)
        );

        $gaEngine->setLogger(new LoggerError());

        $solution = $gaEngine->findSolution();

        return $solution;
    }

    /**
     * @param int[] $value
     * @return float|int
     */
    public function calculateFitness(array $value)
    {
        return $this->calculateTotalDistance($value);
    }

    /**
     * @param int[] $value
     * @return float|int
     */
    public function calculateTotalDistance(array $value)
    {
        $distance = 0;
        /* @var Vector $lastCity, $city */
        $lastCity = null;
        foreach ($value as $cityIndex) {
            $city = $this->cities[$cityIndex];
            if ($lastCity !== null) {
                $distance += $lastCity->distanceTo($city);
            }
            $lastCity = $city;
        }
        return $distance;
    }

    /**
     * @param int[] $value
     * @return float|int
     */
    public function calculateSquaredDistanceSum(array $value)
    {
        $distance = 0;
        /* @var Vector $lastCity, $city */
        $lastCity = null;
        foreach ($value as $cityIndex) {
            $city = $this->cities[$cityIndex];
            if ($lastCity !== null) {
                $distance += $lastCity->distanceToSquared($city);
            }
            $lastCity = $city;
        }
        return $distance;
    }

}
