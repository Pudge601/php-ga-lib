<?php

namespace PW\GA\CrossoverMethod\OrderedList;

use PW\GA\CrossoverMethod\CrossoverMethodInterface;

class OrderCrossover implements CrossoverMethodInterface
{

    /**
     * @param array $parentA
     * @param array $parentB
     * @return array
     */
    public function crossover(array $parentA, array $parentB)
    {
        $valueCount = count($parentA);
        $child      = $parentA;
        $sliceStart = mt_rand(0, $valueCount - 1);
        $sliceEnd   = mt_rand($sliceStart, $valueCount - 1);

        for ($i = 0; $i < $valueCount; $i++) {
            $j = ($i + $sliceStart) % $valueCount;
            if ($j >= $sliceStart && $j <= $sliceEnd) {
                $index = $child[$j];
                $otherKey = array_search($index, $parentB);
                if ($otherKey !== false) {
                    unset($parentB[$otherKey]);
                }
            } else {
                $child[$j] = array_shift($parentB);
            }
        }

        return $child;
    }

}
