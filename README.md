- todo: PHP 8.1
- todo: Parse Annotated Classes
- Separate processes
- Tests

# Mistletoe (PHP Cron Tasks)

[![Latest Version](https://img.shields.io/github/release/chrismichaels84/mistletoe.svg?style=flat-square)](https://github.com/chrismichaels84/mistletoe/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://travis-ci.org/chrismichaels84/mistletoe.svg?branch=master)](https://travis-ci.org/chrismichaels84/mistletoe)
[![Code Coverage](https://scrutinizer-ci.com/g/chrismichaels84/mistletoe/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/chrismichaels84/mistletoe/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/chrismichaels84/mistletoe/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/chrismichaels84/mistletoe/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/michaels/mistletoe.svg?style=flat-square)](https://packagist.org/packages/michaels/mistletoe)

Because Task Management Should Feel Like Christmas...or at least not suck!

Mistletoe provides a super easy-to-use way to manage cron tasks for php.
Simply use the human-readable, fluent builder to add tasks, point ONE cron task to the `mt` file, and watch the magic happen.

## Basic Usage
In mistletoe.php
```php
$planner = (new Mistletoe\TaskPlanner())
    ->add(\Some\Namespace\UpdateWhatever::class)->always()
    ->add('SomeOtherClass')->daily()->at('00:30')
    ->add(new Mistletoe\Command('gulp clean')->daily()->at('1:00')->onProductionOnly()
    ->add(function(){ echo "do something"; })->every5Minutes();
```

In your crontasks, simply add `/path/to/bin/mt run:due` to execute how ever often you want.

### Includes Full Support For

* Simplify ALL cronjobs into a single job
* Environment detection and limiting (only run certain tasks in production)
* Tasks may be console commands, classes, or callables
* Fluent cron schedule builder
* An included TaskRunner
* Easy to create custom task runners
* CLI application
* Fully extensible
* PSR and PHP The Right Way Compliant

## Install
You can use mistletoe in one of three ways:

### As a Phar (Best)

You may download a ready-to-use version of Box as a Phar:

* Go to https://github.com/chrismichaels84/mistletoe/releases/latest
* Download the `mt` under Downloads
* Put it somewhere you'll remember it and/or add it to your path!

### As a Global Composer Install (Better)

This is probably the best way when you have other tools like phpunit and other tools installed in this way:

```sh
$ composer global require michaels/mistletoe --prefer-source
```

You *may* need to add composer's bin directory to your path.

### As a Composer Dependency (Advanced)

You may also install Mistletoe as a dependency for your Composer managed project:

```sh
$ composer require chrismichaels84/mistletoe
```

(or)

```json
{
    "require-dev": {
        "chrismichaels84/mistletoe": "~0.9"
    }
}
```

> Be aware that using this approach requires additional configuration steps to prevent Mistletoe's own dependencies from colliding with your project's dependencies.

## Usage
### As a Command Line Tool
The easiest way to get setup is to create a `mistletoe.php` file to hold your cron tasks.
This file simply returns an instance of `Mistletoe\TaskPlanner` all configured and ready to go.

```php
$planner = (new Mistletoe\TaskPlanner())
    ->add(\Some\Namespace\UpdateWhatever::class)->always()
    ->add('SomeOtherClass')->daily()->at('00:30')
    ->add(new Mistletoe\Command('gulp clean')->daily()->at('1:00')->onProductionOnly()
    ->add(function(){ echo "do something"; })->every5Minutes();
```

From there, you just use the cli commands. Mistletoe assumes the default `mistletoe.php` is
in the current working directory. You may specify a relative or absolute path to any file
that returns a TaskPlanner instance.

For example:

* `mt list:all` - assumes `mistletoe.php` is in this dir
* `mt list:all /full/path/to/file.php` - will use the full path
* `mt list:all ../to/file.php` - will use the relative path

#### Commands

* `list:all` - Lists ALL the commands that are register with info about each
* `list:due` - Lists only the command that are due at that moment with info about each
* `run:due` - Runs whatever commands are due at that moment
* `run:all` - Will force run EVERY command registered
* `run:task Full/Class/Path` - Will run only that one task, due or not
* `help` - Shows this guide

#### Options
If you set the verbosity level to "verbose" or higher, it will print extra information.
```php
mt list:all -v
```
See http://symfony.com/doc/current/components/console/introduction.html#verbosity-levels for more information.

### Setting up the TaskPlanner
The config file only need return an instance of TaskPlanner that has been configured.
There are several methods that make setting it up easy.
For more options, see `Mistletoe\TaskPlanner`.

*Note: All that's required is at least on `add()` method*
```php
$planner = (new TaskPlanner())
    ->setTaskRunner(new CustomTaskRunner()) // Optional: Must implement Mistletoe\Contracts\TaskRunnerInterface
    ->setCurrentEnvironment('development') // Optional
    
    // Add a simple task to run every minute
    ->add('SomeClass')->always()

    // Add Tasks as Classes, Console Commands, or Callables
    ->add(SomeClass::class)->always()
    ->add(new Mistletoe\Command('curl http://google.com/')->always()
    ->add(function () { echo 'something' })->always()
    ->add(new CustomTask())->always() // implements RunnableInterface

    // Add Tasks in a Chain, also as class, command, or callable
    ->add(SomeClass::class)->always()->followedBy('SomeOtherClass')

    // Only run on certain environments
    ->add(SomeClass::class)->always()->onProductionOnly()
    ->add(SomeClass::class)->always()->onDevelopmentOnly()
    ->add(SomeClass::class)->always()->onEnvironment('SomethingCustom')

    // Give it a specific cron schedule
    ->add('SomeTask)->schedule('* 10 * 5 3')

    // Use the Fluent Builder to control scheduling
    ->add('SomeTask)->yearly()->onMonth(6)->onDay(16)
    ->add('SomeTask)->monthly()->onDay(21)
    ->add('SomeTask)->weekly()->onMonday()
    ->add('SomeTask')->daily()->at('1:00')
    ->add('SomeTask')->hourly()->atMinute(10)->andAtMinute(55)
    ->add('SomeTask)->every3Hours() // every{X}Hours()
    ->add('SomeTask)->every22Minutes()
    ->add('SomeTask)->daily()->atMidnight()j
    ->add('SomeTask)->daily()->atHour(7)->andAtHour(12)
    
    // And Mix and Match to your hearts content...
return $planner;
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email :author_email instead of using the issue tracker.

## Credits

- [Michael Wilson](http://github.com/chrismichaels84)
- Special Thanks to [FBS](http://flexmls.com/)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
