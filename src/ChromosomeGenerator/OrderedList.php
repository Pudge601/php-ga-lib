<?php

namespace PW\GA\ChromosomeGenerator;

use PW\GA\Chromosome;

class OrderedList implements ChromosomeGeneratorInterface
{

    /**
     * @var mixed[]
     */
    protected $listValues;

    /**
     * @param mixed[] $listValues
     */
    public function __construct(array $listValues)
    {
        $this->listValues = $listValues;
    }

    /**
     * @param int $numberOfChromosomes
     * @return Chromosome[]
     */
    public function generateChromosomes($numberOfChromosomes)
    {
        $chromosomes = [];
        for ($i = 0; $i < $numberOfChromosomes; $i++) {
            $newChromosome = $this->listValues;
            shuffle($newChromosome);
            $chromosomes[] = new Chromosome($newChromosome);
        }
        return $chromosomes;
    }

}
