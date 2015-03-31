<?php

namespace PW\GA\Test\CrossoverMethod;


use PW\GA\CrossoverMethod\TwoPointCrossover;

class TwoPointCrossoverTest extends \PHPUnit_Framework_TestCase
{

    public function testCrossover()
    {
        $parentA = ['A', 'B', 'C', 'D', 'E', 'F'];
        $parentB = ['1', '2', '3', '4', '5', '6'];

        $crossoverMethod = new TwoPointCrossover();
        $offspring = $crossoverMethod->crossover($parentA, $parentB);

        foreach ($offspring as $childValue) {
            $this->assertEquals(count($parentA), count($childValue));
        }
    }

}
