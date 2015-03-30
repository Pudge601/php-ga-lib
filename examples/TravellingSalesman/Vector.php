<?php

namespace PW\GA\Example\TravellingSalesman;

class Vector
{

    /**
     * @var float
     */
    public $x;

    /**
     * @var float
     */
    public $y;

    /**
     * @param float $x
     * @param float $y
     */
    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @param Vector $other
     * @return float
     */
    public function distanceTo(Vector $other)
    {
        return sqrt($this->distanceToSquared($other));
    }

    /**
     * @param Vector $other
     * @return float
     */
    public function distanceToSquared(Vector $other)
    {
        $xDiff = $this->x - $other->x;
        $yDiff = $this->y - $other->y;
        return ($xDiff * $xDiff) + ($yDiff * $yDiff);
    }
}
