<?php

namespace PW\GA\MutateMethod;


class GeneSwap implements MutateMethodInterface
{

    /**
     * @param mixed[] $value
     * @param float $entropy
     * @return mixed[]
     */
    public function mutate(array $value, $entropy)
    {
        $newValue   = $value;
        $valueCount = count($newValue);
        $maxIndex   = $valueCount - 1;
        $swaps = mt_rand(0, floor($entropy * $valueCount * 0.5));
        for ($i = 0; $i < $swaps; $i++) {
            $indexFrom = mt_rand(0, $maxIndex);
            $indexTo   = mt_rand(0, $maxIndex);
            $tmp = $newValue[$indexFrom];
            $newValue[$indexFrom] = $newValue[$indexTo];
            $newValue[$indexTo]   = $tmp;
        }
        return $newValue;
    }

}
