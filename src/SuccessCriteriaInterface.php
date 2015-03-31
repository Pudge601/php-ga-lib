<?php

namespace PW\GA;

interface SuccessCriteriaInterface
{

    /**
     * @param array $solution
     * @return bool
     */
    public function validateSuccess(array $solution);

}
