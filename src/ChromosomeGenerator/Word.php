<?php

namespace PW\GA\ChromosomeGenerator;


use PW\GA\Chromosome;
use PW\GA\ChromosomeGeneratorInterface;

class Word implements ChromosomeGeneratorInterface
{

    /**
     * @var array
     */
    protected $alphabet;

    /**
     * @var int
     */
    protected $wordLength;

    /**
     * @param array $alphabet
     * @param int $wordLength
     */
    public function __construct(array $alphabet, $wordLength)
    {
        $this->alphabet   = $alphabet;
        $this->wordLength = $wordLength;
    }

    /**
     * @param int $numberOfChromosomes
     * @return Chromosome[]
     */
    public function generateChromosomes($numberOfChromosomes)
    {
        $alphabet = $this->alphabet;
        $chromosomes = [];
        for ($i = 0; $i < $numberOfChromosomes; $i++) {
            shuffle($alphabet);
            $value = array_slice($alphabet, 0, $this->wordLength);
            $chromosomes[] = new Chromosome($value);
        }
        return $chromosomes;
    }
}
