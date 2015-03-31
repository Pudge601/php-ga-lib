<?php

namespace PW\GA\Test\CrossoverMethod\OrderedList;


use PW\GA\CrossoverMethod\OrderedList\OrderCrossover;

class OrderCrossoverTest extends \PHPUnit_Framework_TestCase
{

    public function testCrossover()
    {
        $parentA = ['A', 'B', 'C', 'E', 'F', 'D'];
        $parentB = ['C', 'A', 'B', 'D', 'E', 'F'];

        $edgeRecombination = new OrderCrossover();
        $result = $edgeRecombination->crossover($parentA, $parentB);

        $this->assertEquals(count($parentA), count($result));

        $sortedExpected = $parentA;
        sort($sortedExpected);

        $sortedActual = $result;
        sort($sortedActual);

        $this->assertEquals($sortedExpected, $sortedActual);
    }

}
