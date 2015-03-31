<?php

namespace PW\GA\Test\CrossoverMethod\OrderedList;


use PW\GA\CrossoverMethod\OnePointCrossover;

class OnePointCrossoverTest extends \PHPUnit_Framework_TestCase
{

    public function testCrossover()
    {
        $parentA = ['A', 'B', 'C', 'E', 'F', 'D'];
        $parentB = ['G', 'I', 'H', 'L', 'J', 'K'];

        $crossoverMethod = new OnePointCrossover();
        $result = $crossoverMethod->crossover($parentA, $parentB);

        $this->assertEquals(count($parentA), count($result));
    }

}
