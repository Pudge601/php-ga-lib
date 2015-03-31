#!/usr/bin/env php
<?php

include dirname(dirname(__FILE__)) . '/bootstrap.php';

$target = 'Hello World';

$helloWorld = new \PW\GA\Example\HelloWorld\HelloWorld($target);

$solution = $helloWorld->findSolution([
    \PW\GA\Config::MAX_ITERATIONS   => 15000,
    \PW\GA\Config::POPULATION_COUNT => 150,
    \PW\GA\Config::LOG_FREQUENCY    => 1000,
    \PW\GA\Config::ENTROPY          => 0.5
]);

$solutionString = implode('', $solution);

echo "\nSolution: $solutionString\n\n";
