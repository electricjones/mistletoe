<?php namespace ElectricJones\Mistletoe\Test\Unit;

use ElectricJones\Mistletoe\CronExpression;
use ElectricJones\Mistletoe\Task;
use Exception;
use PHPUnit\Framework\TestCase;


class ExpressionBuilderTest extends TestCase
{
    public function test_it_builds_from_intervals_only()
    {
        foreach (['@yearly', '@annually', '@monthly', '@weekly', '@daily'] as $interval) {
            $task = (new Task('Task'))->setInterval($interval);
            $actual = CronExpression::from($task);
            $expected = new CronExpression($interval);

            $this->assertEquals($expected, $actual, "failed to build from $interval");
        }
    }

    /** @test
     * @throws Exception
     */
    public function it_builds_from_time() // will be daily without any other parameters
    {
        $times = [
            '11:30' => '30 11 * * *',
            '00:25' => '25 00 * * *'
        ];

        foreach ($times as $time => $expression) {
            $task = (new Task('Task'))->setTime($time);
            $actual = CronExpression::from($task);
            $expected = new CronExpression($expression);

            $this->assertEquals($expected, $actual, "failed to build from $time");
        }
    }

    /** @test */
    public function it_builds_from_dates_with_default_times()
    {
        $dates = [
            '11/30' => '0 0 30 11 *',
            '6/15'  => '0 0 15 6 *'
        ];

        foreach ($dates as $date => $expression) {
            $task = (new Task('Task'))->setDate($date);
            $actual = CronExpression::from($task);
            $expected = new CronExpression($expression);

            $this->assertEquals($expected, $actual, "failed to build from $date");
        }
    }

    /** @test
     * @throws Exception
     */
    public function it_builds_from_dates_and_times()
    {
        $dates = [
            '11/30 12:12' => '12 12 30 11 *',
            '6/15 19:22'  => '22 19 15 6 *'
        ];

        foreach ($dates as $date => $expression) {
            $time = explode(' ', $date);
            $task = (new Task('Task'))->setDate($time[0])->setTime($time[1]);

            $actual = CronExpression::from($task);
            $expected = new CronExpression($expression);

            $this->assertEquals($expected, $actual, "failed to build from $date");
        }
    }

    /** @test */
    public function it_handles_some_complex_scenarios()
    {
        /* Scenario: Every days at 7:20 */
        $task = new Task(
            name: 'Task',
            interval: '@daily',
            minutes: '20',
            hours: 7
        );

        $actual = CronExpression::from($task);
        $expected = new CronExpression('20 7 * * *');

        $this->assertEquals($expected, $actual, "failed to build from the first scenario");

        /* Scenario: Every Thursday, Sat, and Sun in June at noon */
        $task = new Task(
            name: 'Task',
            minutes: 00,
            hours: 12,
            months: 6,
            weekdays: [4, '6', '0'],
        );

        $actual = CronExpression::from($task);
        $expected = new CronExpression('0 12 * 6 4,6,0');

        $this->assertEquals($expected, $actual, "failed to build from the weekdays scenario");

        /* Scenario: on the 15 on every months at 15:30 */
        $task = new Task(
            name: 'TaskTwo',
            interval: '@monthly',
            minutes: 30,
            hours: '15',
            days: 12
        );

        $actual = CronExpression::from($task);
        $expected = new CronExpression('30 15 12 * *');

        $this->assertEquals($expected, $actual, "failed to build from the second scenario");

        /* Scenario: Malformed request */
        $task = new Task(
            name: 'TaskThree',
            interval: '@monthly', // this should be ignored...
            minutes: 24,
            hours: 0,
            months: 7,
            days: 1 // ... because of this
        );

        $actual = CronExpression::from($task);
        $expected = new CronExpression('24 0 1 7 *');

        $this->assertEquals($expected, $actual, "failed to build from the third scenario");

        /* Complex: “At every 30 and 59th minutes past the 1, 4 and 8th hours on the 2, 4, 9 and 10th in Jan, Mar and Sep.” */
        $task = new Task(
            name: 'TaskThree',
            minutes: ['30', '59'],
            hours: ['1', 4, '8'],
            months: ['1', '3', 9],
            days: [2, 4, 9]
        );

        $actual = CronExpression::from($task);
        $expected = new CronExpression('30,59 1,4,8 2,4,9 1,3,9 *');

        $this->assertEquals($expected, $actual, "failed to build from the complex scenario");
    }
}
