<?php

namespace PW\GA\CrossoverMethod\OrderedList;

use PW\GA\CrossoverMethodInterface;

class EdgeRecombination implements CrossoverMethodInterface
{

    /**
     * @param array $parentA
     * @param array $parentB
     * @return array
     */
    public function crossover(array $parentA, array $parentB)
    {
        $edges = $this->unionEdges($this->findEdges($parentA), $this->findEdges($parentB));

        $parentLength = count($parentA);
        $mainParent = mt_rand(0, 1) ? $parentA : $parentB;
        $node = array_shift($mainParent);
        $child = [];
        while (count($child) < $parentLength) {
            $child[] = $node;
            $edges = $this->removeFromMatrix($node, $edges);
            $neighbours = $edges[$node];
            unset($edges[$node]);
            if (!empty($neighbours)) {
                $node = $this->findLoneliestNeighbour($neighbours, $edges);
            } else {
                $node = array_rand($edges);
            }
        }

        return $child;
    }

    /**
     * @param array $value
     * @return array
     */
    public function findEdges(array $value)
    {
        $edges = [];
        $valueLength = count($value);
        foreach ($value as $index => $node) {
            $edges[$node] = [
                $value[($index + ($valueLength - 1)) % $valueLength],
                $value[($index + 1) % $valueLength],
            ];
        }
        return $edges;
    }

    /**
     * @param array $adjacencyMatrixA
     * @param array $adjacencyMatrixB
     * @return array
     */
    public function unionEdges($adjacencyMatrixA, $adjacencyMatrixB)
    {
        $merged = [];

        foreach ($adjacencyMatrixA as $node => $edgesA) {
            $edgesB = $adjacencyMatrixB[$node];
            $merged[$node] = array_values(array_unique(array_merge($edgesA, $edgesB)));
        }

        return $merged;
    }

    /**
     * @param mixed $node
     * @param array $adjencyMatrix
     * @return array
     */
    public function removeFromMatrix($node, $adjencyMatrix)
    {
        foreach ($adjencyMatrix as &$nodeList) {
            $index = array_search($node, $nodeList);
            if ($index !== false) {
                array_splice($nodeList, $index, 1);
            }
        }
        return $adjencyMatrix;
    }

    /**
     * @param array $neighbours
     * @param array $adjacencyMatrix
     * @return mixed
     */
    public function findLoneliestNeighbour($neighbours, $adjacencyMatrix)
    {
        $chosenNode = null;
        $chosenNeighbourCount = null;
        foreach ($neighbours as $node) {
            $neighbourCount = count($adjacencyMatrix[$node]);
            if ($chosenNeighbourCount === null ||
                $neighbourCount < $chosenNeighbourCount ||
                ($neighbourCount === $chosenNeighbourCount && mt_rand(0, 1))
            ) {
                $chosenNode = $node;
                $chosenNeighbourCount = $neighbourCount;
            }
        }
        return $chosenNode;
    }
}
