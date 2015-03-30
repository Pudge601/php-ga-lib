<?php

namespace PW\GA\Example;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class LoggerError implements LoggerInterface
{
    use LoggerTrait;

    public function log($level, $message, array $context = array())
    {
        error_log($message);
    }
}
