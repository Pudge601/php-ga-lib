<?php

namespace PW\GA;

interface SuccessCriteriaInterface
{

    /**
     * @param Chromosome $solution
     * @return bool
     */
    public function validateSuccess(Chromosome $solution);

}
