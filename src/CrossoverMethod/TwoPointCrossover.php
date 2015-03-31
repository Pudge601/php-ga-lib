<?php

namespace PW\GA\CrossoverMethod;

use PW\GA\CrossoverMethodInterface;

class TwoPointCrossover implements CrossoverMethodInterface
{

    /**
     * @param array $parentA
     * @param array $parentB
     * @return array
     */
    public function crossover(array $parentA, array $parentB)
    {
        $valueCount      = count($parentA);
        $crossoverPoint1 = mt_rand(0, ceil($valueCount / 2));
        $crossoverPoint2 = mt_rand(ceil($valueCount / 2), $valueCount - 1);

        return array_merge(
            array_slice($parentA, 0, $crossoverPoint1),
            array_slice($parentB, $crossoverPoint1, $crossoverPoint2 - $crossoverPoint1),
            array_slice($parentA, $crossoverPoint2)
        );
    }

}
