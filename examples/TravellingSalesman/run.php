#!/usr/bin/env php
<?php

include dirname(dirname(__FILE__)) . '/bootstrap.php';

$cityCoords = [[469,473], [570,490], [475,560], [452,467], [447,433], [475,204], [474,128], [392,46], [487,130], [521,217], [559,32], [449,92], [11,0], [188,109], [160,322], [45,214], [46,542], [10,421], [74,564], [136,550]];

$travellingSalesman = (new \PW\GA\Example\TravellingSalesman\TravellingSalesman())
    ->setCities($cityCoords);

$cities = $travellingSalesman->getCities();

$solution = $travellingSalesman->findSolution([
    \PW\GA\Config::POPULATION_COUNT => 100,
    \PW\GA\Config::LOG_FREQUENCY    => 1000,
    \PW\GA\Config::CHURN_ENTROPY    => 0.6,
    \PW\GA\Config::MUTATE_ENTROPY   => 0.4,
], 10000);

$totalDistance = $travellingSalesman->calculateTotalDistance($solution);

$citiesOrdered = [];
/* @var \PW\GA\Example\TravellingSalesman\Vector $city */
foreach ($solution as $cityIndex) {
    $city = $cities[$cityIndex];
    $citiesOrdered[] = "[{$city->x},{$city->y}]";
}

echo "\nSolution: [" . implode(', ', $citiesOrdered) . "] (total distance: $totalDistance)\n\n";
