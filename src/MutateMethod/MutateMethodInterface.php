<?php

namespace PW\GA\MutateMethod;


interface MutateMethodInterface
{

    /**
     * @param mixed[] $value
     * @param float $entropy
     * @return mixed[]
     */
    public function mutate(array $value, $entropy);

}
