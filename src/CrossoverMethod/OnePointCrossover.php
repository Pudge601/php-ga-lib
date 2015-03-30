<?php

namespace PW\GA\CrossoverMethod;

class OnePointCrossover implements CrossoverMethodInterface
{

    /**
     * @param array $parentA
     * @param array $parentB
     * @return array
     */
    public function crossover(array $parentA, array $parentB)
    {
        $valueCount     = count($parentA);
        $crossoverPoint = rand(0, $valueCount - 1);

        return array_merge(
            array_slice($parentA, 0, $crossoverPoint),
            array_slice($parentB, $crossoverPoint)
        );
    }

}