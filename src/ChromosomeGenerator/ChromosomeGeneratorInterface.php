<?php

namespace PW\GA\ChromosomeGenerator;

use PW\GA\Chromosome;

interface ChromosomeGeneratorInterface
{

    /**
     * @param int $numberOfChromosomes
     * @return Chromosome[]
     */
    public function generateChromosomes($numberOfChromosomes);

}
