<?php

namespace PW\GA;

interface ChromosomeGeneratorInterface
{

    /**
     * @param int $numberOfChromosomes
     * @return Chromosome[]
     */
    public function generateChromosomes($numberOfChromosomes);

}
