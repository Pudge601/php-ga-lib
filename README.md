Genetic Algorithm Library for PHP
=================================

A generic library for solving problems using genetic algorithms in PHP

Installation
------------

Download/clone the repository, and run `composer install`.

Examples can be found in the `examples` directory.

Usage
-----

This shows a basic usage of this library to solve a trivial hello world problem.

The first thing required to use this library is some method of calculating the fitness of each chromosome's value.
This is done by providing something which implements `\PW\GA\FitnessCalculatorInterface`. 

```php
class HelloFitnessCalculator implements \PW\GA\FitnessCalculatorInterface
{
    protected $target;

    public function __construct($target)
    {
        $this->target = $target;
    }

    public function calculateFitness(array $value)
    {
        $stringValue = implode('', $value);
        $target      = $this->target;
        similar_text($stringValue, $target, $percent);
        return $percent / 100;
    }
}
```

Next, if it's possible to have a 'right' answer, you will want the engine to stop when the target is reached.
This is done by providing something which implements `\PW\GA\SuccessCriteriaInterface`.

```php
class HelloSuccessCriteria implements \PW\GA\SuccessCriteriaInterface
{
    protected $target;

    public function __construct($target)
    {
        $this->target = $target;
    }

    public function validateSuccess(\PW\GA\Chromosome $fittest)
    {
        return implode('', $fittest->getValue()) === $this->target;
    }
}
```

Now you can create an instance of the genetic algorithm engine, configure, and run;

```php
$target   = 'Hello World';
$alphabet = array_unique(array_merge(range('A', 'z'), str_split($target)));

$gaEngine = new \PW\GA\GeneticAlgorithm(
    new HelloFitnessCalculator($target),
    new \PW\GA\CrossoverMethod\TwoPointCrossover(),
    new \PW\GA\MutateMethod\ModifyWord($alphabet),
    new \PW\GA\Config([
        \PW\GA\Config::SORT_DIR         => \PW\GA\GeneticAlgorithm::SORT_DIR_DESC,
        \PW\GA\Config::POPULATION_COUNT => 1000,
        \PW\GA\Config::CHURN_ENTROPY    => 0.6,
        \PW\GA\Config::MUTATE_ENTROPY   => 0.4,
        \PW\GA\Config::WEIGHTING_COEF   => 0.3,
    ])
);

$gaEngine->initPopulation(new \PW\GA\ChromosomeGenerator\Word($alphabet, strlen($target)))
    ->optimiseUntil(new HelloSuccessCriteria($target), 10000);

$fittest = $gaEngine->getFittest()->getValue();
```

Testing
-------

The testing suite is not yet fully comprehensive (practically non-existent), but this may be looked at some day
