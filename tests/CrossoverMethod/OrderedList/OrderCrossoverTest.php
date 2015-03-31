<?php

namespace PW\GA\Test\CrossoverMethod\OrderedList;


use PW\GA\CrossoverMethod\OrderedList\OrderCrossover;

class OrderCrossoverTest extends \PHPUnit_Framework_TestCase
{

    public function testCrossover()
    {
        $parentA = ['A', 'B', 'C', 'E', 'F', 'D'];
        $parentB = ['C', 'A', 'B', 'D', 'E', 'F'];

        $crossoverMethod = new OrderCrossover();
        $offspring = $crossoverMethod->crossover($parentA, $parentB);

        $sortedExpected = $parentA;
        sort($sortedExpected);

        foreach ($offspring as $childValue) {
            $this->assertEquals(count($parentA), count($childValue));

            $sortedActual = $childValue;
            sort($sortedActual);

            $this->assertEquals($sortedExpected, $sortedActual);

        }
    }

}
