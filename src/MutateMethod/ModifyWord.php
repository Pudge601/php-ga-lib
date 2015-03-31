<?php

namespace PW\GA\MutateMethod;

use PW\GA\MutateMethodInterface;

class ModifyWord implements MutateMethodInterface
{

    /**
     * @var array
     */
    protected $alphabet;

    /**
     * @param array $alphabet
     */
    public function __construct(array $alphabet)
    {
        $this->alphabet = $alphabet;
    }

    /**
     * @param mixed[] $value
     * @param float $entropy
     * @return mixed[]
     */
    public function mutate(array $value, $entropy)
    {
        $valueLength = count($value);
        $charChanges = ceil($entropy * ($valueLength / 2));
        $alphabet    = $this->alphabet;
        for ($i = 0; $i < $charChanges; $i++) {
            $charIndex = mt_rand(0, $valueLength - 2);
            $newChar   = $alphabet[array_rand($alphabet)];
            $value[$charIndex] = $newChar;
        }
        return $value;
    }

}
