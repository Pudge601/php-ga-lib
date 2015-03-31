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

        $hasParentA = false;
        $hasParentB = false;
        foreach ($result as $char) {
            if (array_search($char, $parentA) !== false) {
                $hasParentA = true;
            } else if (array_search($char, $parentB) !== false) {
                $hasParentB = true;
            }
        }
        $this->assertTrue($hasParentA && $hasParentB, 'Child does not contain characters from both parents');
    }

}
