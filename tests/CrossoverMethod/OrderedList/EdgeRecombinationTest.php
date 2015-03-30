<?php

namespace PW\GA\Test\CrossoverMethod\OrderedList;


use PW\GA\CrossoverMethod\OrderedList\EdgeRecombination;

class EdgeRecombinationTest extends \PHPUnit_Framework_TestCase
{

    public function testFindEdges()
    {
        $list = [
            'A',
            'C',
            'D',
            'B',
            'F',
            'E',
        ];

        $expected = [
            'A' => ['E', 'C'],
            'C' => ['A', 'D'],
            'D' => ['C', 'B'],
            'B' => ['D', 'F'],
            'F' => ['B', 'E'],
            'E' => ['F', 'A'],
        ];

        $edgeRecombination = new EdgeRecombination();
        $actual = $edgeRecombination->findEdges($list);

        $this->assertEquals(
            $this->sortAdjacencyMatrix($expected),
            $this->sortAdjacencyMatrix($actual)
        );
    }

    public function testUnionEdges()
    {
        $adjacencyMatrixA = [
            'A' => ['B', 'D'],
            'B' => ['A', 'C'],
            'C' => ['B', 'E'],
            'D' => ['F', 'A'],
            'E' => ['C', 'F'],
            'F' => ['E', 'D'],
        ];

        $adjacencyMatrixB = [
            'A' => ['C', 'B'],
            'B' => ['A', 'D'],
            'C' => ['F', 'A'],
            'D' => ['B', 'E'],
            'E' => ['D', 'F'],
            'F' => ['E', 'C'],
        ];

        $expected = [
            'A' => ['B', 'C', 'D'],
            'B' => ['A', 'C', 'D'],
            'C' => ['A', 'B', 'E', 'F'],
            'D' => ['A', 'B', 'E', 'F'],
            'E' => ['C', 'D', 'F'],
            'F' => ['C', 'D', 'E'],
        ];

        $edgeRecombination = new EdgeRecombination();
        $actual = $edgeRecombination->unionEdges($adjacencyMatrixA, $adjacencyMatrixB);

        $this->assertEquals(
            $this->sortAdjacencyMatrix($expected),
            $this->sortAdjacencyMatrix($actual)
        );
    }

    public function testRemoveFromMatrix()
    {
        $matrix = [
            'A' => ['B', 'C', 'D'],
            'B' => ['A', 'C', 'D'],
            'C' => ['A', 'B', 'E', 'F'],
            'D' => ['A', 'B', 'E', 'F'],
            'E' => ['C', 'D', 'F'],
            'F' => ['C', 'D', 'E'],
        ];

        $node = 'B';

        $expected = [
            'A' => ['C', 'D'],
            'B' => ['A', 'C', 'D'],
            'C' => ['A', 'E', 'F'],
            'D' => ['A', 'E', 'F'],
            'E' => ['C', 'D', 'F'],
            'F' => ['C', 'D', 'E'],
        ];

        $edgeRecombination = new EdgeRecombination();
        $actual = $edgeRecombination->removeFromMatrix($node, $matrix);


        $this->assertEquals(
            $this->sortAdjacencyMatrix($expected),
            $this->sortAdjacencyMatrix($actual)
        );
    }

    public function testCrossover()
    {
        $parentA = ['A', 'B', 'C', 'E', 'F', 'D'];
        $parentB = ['C', 'A', 'B', 'D', 'E', 'F'];

        $edgeRecombination = new EdgeRecombination();
        $result = $edgeRecombination->crossover($parentA, $parentB);

        $this->assertEquals(count($parentA), count($result));

        $sortedExpected = $parentA;
        sort($sortedExpected);

        $sortedActual = $result;
        sort($sortedActual);

        $this->assertEquals($sortedExpected, $sortedActual);
    }

    protected function sortAdjacencyMatrix($adjacencyMatrix)
    {
        ksort($adjacencyMatrix);
        foreach ($adjacencyMatrix as &$nodes) {
            sort($nodes);
        }
        return $adjacencyMatrix;
    }

}
