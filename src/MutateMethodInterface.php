<?php

namespace PW\GA;


interface MutateMethodInterface
{

    /**
     * @param mixed[] $value
     * @param float $entropy
     * @return mixed[]
     */
    public function mutate(array $value, $entropy);

}
