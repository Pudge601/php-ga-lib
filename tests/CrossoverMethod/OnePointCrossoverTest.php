<?php

namespace PW\GA\Test\CrossoverMethod;


use PW\GA\CrossoverMethod\OnePointCrossover;

class OnePointCrossoverTest extends \PHPUnit_Framework_TestCase
{

    public function testCrossover()
    {
        $parentA = ['A', 'B', 'C', 'E', 'F', 'D'];
        $parentB = ['G', 'I', 'H', 'L', 'J', 'K'];

        $crossoverMethod = new OnePointCrossover();
        $offspring = $crossoverMethod->crossover($parentA, $parentB);

        foreach ($offspring as $childValue) {
            $this->assertEquals(count($parentA), count($childValue));
        }
    }

}
