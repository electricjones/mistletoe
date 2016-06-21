<?php namespace Mistletoe\Test;

use Mistletoe\ExpressionBuilder;
use Mistletoe\TaskBag;
use PHPUnit_Framework_TestCase;

class ExpressionBuilderTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function TestBuildFromStringExpression()
    {
        $builder = new ExpressionBuilder();
        $actual = $builder->buildFrom('1 1 1 1 1');
        $this->assertEquals(\Cron\CronExpression::factory('1 1 1 1 1'), $actual, 'failed to build from first expression');

        $builder = new ExpressionBuilder();
        $actual = $builder->buildFrom('2 2 * * *');
        $this->assertEquals(\Cron\CronExpression::factory('2 2 * * *'), $actual, 'failed to build from second expression');
    }
    
    /** @test */
    public function TestBuildsFromIntervalsOnly()
    {
        foreach (['@yearly', '@annually', '@monthly', '@weekly', '@daily'] as $interval) {
            $builder = new ExpressionBuilder();
            $builder->setTaskBag(
                (new TaskBag('Task'))->setInterval($interval)
            );
            $actual = $builder->build();
            $expected = \Cron\CronExpression::factory($interval);
            
            $this->assertEquals($expected, $actual, "failed to build from $interval");
        }
    }
    
    /** @test */
    public function TestBuildsFromTime() // will be daily without any other parameters
    {
        $times = [
            '11:30' => '30 11 * * *',
            '24:25' => '25 24 * * *'
        ];

        foreach ($times as $time => $expression) {
            $builder = new ExpressionBuilder();
            $builder->setTaskBag(
                (new TaskBag('Task'))->setTime($time)
            );
            $actual = $builder->build();
            $expected = \Cron\CronExpression::factory($expression);

            $this->assertEquals($expected, $actual, "failed to build from $time");
        }
    }

    /** @test */
    public function TestBuildsFromDatesWithDefaultTimes()
    {
        $dates = [
            '11/30' => '0 0 30 11 *',
            '6/15' => '0 0 15 6 *'
        ];

        foreach ($dates as $date => $expression) {
            $builder = new ExpressionBuilder();
            $builder->setTaskBag(
                (new TaskBag('Task'))->setDate($date)
            );
            $actual = $builder->build();
            $expected = \Cron\CronExpression::factory($expression);

            $this->assertEquals($expected, $actual, "failed to build from $date");
        }
    }

    /** @test */
    public function TestBuildsFromDatesWithSetTimes()
    {
        $dates = [
            //date time -- just for testing purposes!
            '11/30 12:12' => '12 12 30 11 *',
            '6/15 19:22' => '22 19 15 6 *'
        ];

        foreach ($dates as $date => $expression) {
            $builder = new ExpressionBuilder();
            $time = explode(' ', $date);
            $builder->setTaskBag(
                (new TaskBag('Task'))->setDate($time[0])->setTime($time[1])
            );
            $actual = $builder->build();
            $expected = \Cron\CronExpression::factory($expression);

            $this->assertEquals($expected, $actual, "failed to build from $date");
        }
    }

    /** @test */
    public function TestSomeComplexScenarios()
    {
        /* Scenario: Every day at 7:20 */
        $builder = new ExpressionBuilder();
        $builder->setTaskBag(
            new TaskBag([
                'task' => 'Task',
                'interval' => '@daily',
                'hour' => 7,
                'minute' => 20
            ])
        );
        $actual = $builder->build();
        $expected = \Cron\CronExpression::factory('20 7 * * *');

        $this->assertEquals($expected, $actual, "failed to build from the first scenario");

        /* Scenario: Every Thursday, Sat, and Sun in June at noon */
        $builder = new ExpressionBuilder();
        $builder->setTaskBag(
            new TaskBag([
                'task' => 'Task',
                'hour' => 12,
                'minute' => 00,
                'weekday' => '4,6,0',
                'month' => 6
            ])
        );
        $actual = $builder->build();
        $expected = \Cron\CronExpression::factory('0 12 * 6 4,6,0');

        $this->assertEquals($expected, $actual, "failed to build from the weekday scenario");


        /* Scenario: on the 15 on ever month at 15:30 */
        $builder = new ExpressionBuilder();
        $builder->setTaskBag(
            new TaskBag([
                'task' => 'TaskTwo',
                'interval' => '@monthly',
                'hour' => '15',
                'minute' => 30,
                'day' => 12
            ])
        );
        $actual = $builder->build();
        $expected = \Cron\CronExpression::factory('30 15 12 * *');

        $this->assertEquals($expected, $actual, "failed to build from the second scenario");


        /* Scenario: Malformed request */
        $builder = new ExpressionBuilder();
        $builder->setTaskBag(
            new TaskBag([
                'task' => 'TaskThree',
                'interval' => '@monthly', // this should be ignored...
                'hour' => 0,
                'minute' => 24,
                'day' => 1,
                'month' => 7 // ... because of this
            ])
        );
        $actual = $builder->build();
        $expected = \Cron\CronExpression::factory('24 0 1 7 *');

        $this->assertEquals($expected, $actual, "failed to build from the third scenario");

        /* Complex: “At every 30 and 59th minute past the 1, 4 and 8th hour on the 2, 4, 9 and 10th in Jan, Mar and Sep.” */
        $builder = new ExpressionBuilder();
        $builder->setTaskBag(
            new TaskBag([
                'task' => 'TaskThree',
                'minute' => '30,59',
                'hour' => '1,4,8',
                'day' => '2,4,9',
                'month' => '1,3,9'
            ])
        );
        $actual = $builder->build();
        $expected = \Cron\CronExpression::factory('30,59 1,4,8 2,4,9 1,3,9 *');

        $this->assertEquals($expected, $actual, "failed to build from the complex scenario");
    }
}

