#!/usr/bin/env php
<?php

include dirname(dirname(__FILE__)) . '/bootstrap.php';

$target = 'Hello World';

$helloWorld = new \PW\GA\Example\HelloWorld\HelloWorld($target);

$solution = $helloWorld->findSolution([
    \PW\GA\Config::POPULATION_COUNT => 100,
    \PW\GA\Config::CHURN_ENTROPY    => 0.6,
    \PW\GA\Config::MUTATE_ENTROPY   => 0.4,
    \PW\GA\Config::WEIGHTING_COEF   => 0.4,
], 10000);

$solutionString = implode('', $solution);

echo "\nSolution: $solutionString\n\n";
