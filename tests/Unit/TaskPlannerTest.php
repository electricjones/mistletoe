<?php namespace ElectricJones\Mistletoe\Test\Unit;

use ElectricJones\Mistletoe\CronExpression;
use ElectricJones\Mistletoe\Task;
use ElectricJones\Mistletoe\TaskPlanner;
use PHPUnit\Framework\TestCase;


class TaskPlannerTest extends TestCase
{
    /** @test */
    public function it_adds_new_tasks()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1');
        $planner->add('Task2');

        $expected = [
            'Task1' => new Task('Task1'),
            'Task2' => new Task('Task2'),
        ];

        $this->assertEquals($expected, $planner->getTasks(), "failed to return correct list of tasks");
    }

    /** @test */
    public function it_schedules()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->schedule('1 1 1 * *');

        $this->assertEquals(new CronExpression('1 1 1 * *'), $planner->getTask('Task1')->getCronExpression(), "failed to schedule a single task");
    }

//    /** @test */
//    public function TestIncrementingClosures()
//    {
//        $planner = new TaskPlanner();
//        $planner->add(function() { return 1; })->schedule('1 1 1 * *');
//        $planner->add(function() { return 2; })->schedule('1 1 1 * *');
//        $planner->add(function() { return 3; })->schedule('1 1 1 * *');
//
//        $expected = [
//            '_task1' => (new Task('_task0'))->setName(function () {
//                return 1;
//            })->setCronExpression('1 1 1 * *'),
//            '_task2' => (new Task('_task0'))->setName(function () {
//                return 2;
//            })->setCronExpression('1 1 1 * *'),
//            '_task3' => (new Task('_task0'))->setName(function () {
//                return 3;
//            })->setCronExpression('1 1 1 * *'),
//        ];
//
//        $this->assertEquals($expected, $planner->getTasks(), "failed to schedule a closure task");
//    }

    /** @test */
    public function TestIntervals()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->yearly();
        $planner->add('Task2')->annually();
        $planner->add('Task3')->monthly();
        $planner->add('Task4')->weekly();
        $planner->add('Task5')->daily();
        $planner->add('Task6')->hourly();

        $intervals = ['@yearly', '@yearly', '@monthly', '@weekly', '@daily', '@hourly'];

        $i = 1;
        foreach ($intervals as $interval) {
            $this->assertEquals($interval, $planner->getTask("Task$i")->getInterval(), "failed to set $interval interval");
            $i++;
        }
    }

    /** @test */
    public function TestTimes()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->at('11:21');
        $planner->add('Task2')->at('4:19');
        $planner->add('Task3')->at('22:07');

        $this->assertEquals((new Task('Task1'))->setTime('11:21'), $planner->getTask('Task1'), "failed to set the first time");
        $this->assertEquals((new Task('Task2'))->setTime('4:19'), $planner->getTask('Task2'), "failed to set the second time");
        $this->assertEquals((new Task('Task3'))->setTime('22:07'), $planner->getTask('Task3'), "failed to set the third time");
    }

    /** @test */
    public function TestTimeAliases()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->atMidnight();
        $planner->add('Task2')->atNoon();

        $this->assertEquals((new Task('Task1'))->setTime('24:00'), $planner->getTask('Task1'), "failed to set midnight");
        $this->assertEquals((new Task('Task2'))->setTime('12:00'), $planner->getTask('Task2'), "failed to set noon");
    }

    /** @test */
    public function TestDates()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->on('12-15');
        $planner->add('Task2')->on('2/19');
        $planner->add('Task3')->on('7-28');

        $this->assertEquals((new Task('Task1'))->setMonths(12)->setDays(15), $planner->getTask('Task1'), "failed to set the first time");
        $this->assertEquals((new Task('Task2'))->setMonths(2)->setDays(19), $planner->getTask('Task2'), "failed to set the second time: make sure it normalized the format");
        $this->assertEquals((new Task('Task3'))->setMonths(7)->setDays(28), $planner->getTask('Task3'), "failed to set the third time: make sure it normalized the month");
    }

    /** @test */
    public function TestAppendingDates()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->on('12-15')->andOn('1/20');
        $planner->add('Task2')->on('2/19')->andOn('2/21');
        $planner->add('Task3')->on('7-28')->andOn('3-22');

        $this->assertEquals((new Task('Task1'))->addMonth(12)->addDay(15)->addMonth(1)->addDay(20), $planner->getTask('Task1'), "failed to add the first time");
        $this->assertEquals((new Task('Task2'))->addMonth(2)->addDay(19)->addMonth(2)->addDay(21), $planner->getTask('Task2'), "failed to add the second time: make sure it normalized the format");
        $this->assertEquals((new Task('Task3'))->addMonth(7)->addDay(28)->addMonth(3)->addDay(22), $planner->getTask('Task3'), "failed to set the third time: make sure it normalized the month");
    }

    /** @test */
    public function TestDayOfTheMonth()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->onDay(7);

        $this->assertEquals([
            'Task1' => (new Task('Task1'))->setDays(7)
        ], $planner->getTasks(), "failed to return an array with the correct tasks"); // tested against all tasks for variety
    }

    /** @test */
    public function TestAppendingDay()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->onDay(7)->andOnDay(2);

        $this->assertEquals([
            'Task1' => (new Task('Task1'))->setDays(7)->addDay(2)
        ], $planner->getTasks(), "failed to return an array with the correct tasks"); // tested against all tasks for variety
    }

    /** @test */
    public function TestWeekday()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->onWeekday(3)->onSaturday();

        $this->assertEquals([
            'Task1' => (new Task('Task1'))->addWeekday(3)->addWeekday(6)
        ], $planner->getTasks(), "failed to return an array with the correct tasks"); // tested against all tasks for variety
    }

    /** @test */
    public function TestOnEnvironment()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->onEnvironment(TaskPlanner::PRODUCTION_ENVIRONMENT);
        $planner->add('Task2')->onEnvironment(TaskPlanner::DEVELOPMENT_ENVIRONMENT);
        $planner->add('Task3')->onEnvironment('custom_environment');

        $this->assertEquals((new Task('Task1'))->addEnvironment('PRODUCTION'), $planner->getTask('Task1'), "failed to set production");
        $this->assertEquals((new Task('Task2'))->addEnvironment('DEVELOPMENT'), $planner->getTask('Task2'), "failed to set development");
        $this->assertEquals((new Task('Task3'))->addEnvironment('CUSTOM_ENVIRONMENT'), $planner->getTask('Task3'), "failed to set custom");
    }

    /** @test */
    public function TestEnvironmentAliases()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->onProductionOnly();
        $planner->add('Task2')->onDevelopmentOnly();

        $this->assertEquals((new Task('Task1'))->setEnvironments('PRODUCTION'), $planner->getTask('Task1'), "failed to set production");
        $this->assertEquals((new Task('Task2'))->setEnvironments('DEVELOPMENT'), $planner->getTask('Task2'), "failed to set development");
    }

    /** @test */
    public function TestFollowedBy()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->followedBy('Task2')->followedBy('Task3');

        $this->assertEquals((new Task('Task1'))->setFollowedBy(['Task2', 'Task3']), $planner->getTask('Task1'), "failed to set followed by tasks");
    }

    /** @test */
    public function TestComplexExample()
    {
        $planner = new TaskPlanner();
        $planner->add('SomeTask')
            ->yearly()
            ->on('7-12')
            ->atMidnight()
            ->onProductionOnly()
            ->followedBy('Task2')
            ->followedBy('Task3');

        $planner->add('AnotherTask')
            ->daily()
            ->at('13:14')
            ->onEnvironment('staging');

        $planner->add('ThirdTask')
            ->onSaturday()->onSunday()
            ->onMonth([2,4,6])
            ->at('13:14');

        $expected = [
            'SomeTask'    => (new Task('SomeTask'))->setInterval('@yearly')->setDays(12)->setMonths(7)->setTime('24:00')->setEnvironments(TaskPlanner::PRODUCTION_ENVIRONMENT)->setFollowedBy(['Task2', 'Task3']),
            'AnotherTask' => (new Task('AnotherTask'))->setInterval('@daily')->setTime('13:14')->addEnvironment('STAGING'),
            'ThirdTask' => (new Task('ThirdTask'))->setWeekdays([0, 6])->setMonths(['2', 4, '6'])->setTime('13:14')
        ];

        $this->assertEquals($expected, $planner->getTasks(), 'failed to create complex bags');
    }

    /** @test */
    public function TestXIncrements()
    {
        $planner = new TaskPlanner();
        $planner->add('Task1')->daily()->every4Minutes();
        $planner->add('Task2')->daily()->every3Hours();

        $expected = [
            'Task1' => (new Task('Task1'))->setInterval('@daily')->setMinutes('*/4'),
            'Task2' => (new Task('Task2'))->setInterval('@daily')->setHours('*/3'),
        ];

        $this->assertEquals($expected, $planner->getTasks(), 'failed to use increment');
    }
}
