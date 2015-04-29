<?php

namespace PW\GA\Test\WeightedSelector;

use PW\GA\WeightedSelector\WeightArray;

class WeightArrayTest extends \PHPUnit_Framework_TestCase
{
    public function testFrequencyDistribution()
    {
        $weightedSelector = new WeightArray();

        $populationCount = 15;

        $weightedSelector->init($populationCount, 1);

        $indices = $this->selectIndices($weightedSelector, 10000);

        $lastFrequency = null;
        $trend = 0;
        foreach ($indices as $index => $frequency) {
            if ($lastFrequency !== null) {
                $trend += $lastFrequency - $frequency;
            }
            $lastFrequency = $frequency;
        }

        $this->assertTrue($trend > 0, 'Frequency should descend for each index');
    }

    protected function selectIndices(WeightArray $weightedSelector, $numIndices)
    {
        $indices = [];
        for ($i = 0; $i < $numIndices; $i++) {
            $index = $weightedSelector->nextIndex();
            if (!isset($indices[$index])) {
                $indices[$index] = 0;
            }
            $indices[$index]++;
        }
        return $indices;
    }
}
