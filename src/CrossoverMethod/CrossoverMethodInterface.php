<?php

namespace PW\GA\CrossoverMethod;

interface CrossoverMethodInterface
{

    /**
     * @param array $parentA
     * @param array $parentB
     * @return array
     */
    public function crossover(array $parentA, array $parentB);

}
