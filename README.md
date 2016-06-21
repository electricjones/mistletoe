# Mistletoe (PHP Cron Tasks)
Because Task Management Should Feel Like Christmas...

...or at least not suck!

## A Preview
```php
$planner = (new Mistletoe\TaskPlanner())
    ->add(\Some\Namespace\UpdateWhatever::class)->always()
    ->add('SomeOtherClass')->daily()->at('00:30')
    ->add(new Mistletoe\Command('gulp clean')->daily()->at('1:00')->onProductionOnly()
    ->add(function(){ echo "do something"; })->every5Minutes();
```

### With Full Support For
  * Simplify ALL cronjobs into a single job
  * Environment detection
  * Tasks may be commands, classes, or callables
  * Fluent cron builder
  * An included TaskRunner
  * Easy to create custom task runners
  * CLI application
  * Even run without using cronjob at all (sort of)

## Coming this week
Don't worry, a fully production-ready release is coming before July 2016.