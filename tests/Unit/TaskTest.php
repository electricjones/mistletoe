<?php namespace ElectricJones\Mistletoe\Test\Unit;

/* The Getter and Setter methods are all tested in `TaskPlannerTest`. No need to duplicate */

use ElectricJones\Mistletoe\CronExpression;
use ElectricJones\Mistletoe\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    /** @test */
    public function it_builds_a_new_task_with_name()
    {
        $task = new Task('Task');
        $this->assertEquals('Task', $task->getCallable(), 'failed to set task name');
    }

    /* Appending Values */
    /** @test */
    public function TestAddMonth()
    {
        $task = new Task('Task');
        $task->addMonth(1)->addMonth(2)->addMonth(3);

        $this->assertEquals(['1', '2', '3'], $task->getMonths(), 'failed to append months');
    }

    /** @test */
    public function TestAddDay()
    {
        $task = new Task('Task');
        $task->addDay(1)->addDay(2)->addDay(3);

        $this->assertEquals(['1', '2', '3'], $task->getDays(), 'failed to append day');
    }

    /** @test */
    public function TestAddHour()
    {
        $task = new Task('Task');
        $task->addHour(1)->addHour(2)->addHour(3);

        $this->assertEquals(['1', '2', '3'], $task->getHours(), 'failed to append hours');
    }

    /** @test */
    public function TestAddMintue()
    {
        $task = new Task('Task');
        $task->addMinute(1)->addMinute(2)->addMinute(3);

        $this->assertEquals(['1', '2', '3'], $task->getMinutes(), 'failed to append minutes');
    }

    /** @test */
    public function TestSetCronExpression()
    {
        // From a CronExpression instance
        $task = new Task('Task');
        $expression = new CronExpression('1 2 3 4 5');
        $task->setCronExpression($expression);
        $this->assertEquals(new CronExpression('1 2 3 4 5'), $task->getCronExpression(), 'failed to set cron expression from instance');
    }

    /** @test */
    // This builds a CronExpression object from a full bag. The ExpressionBuilder is mocked
//    public function TestGetCronExpression()
//    {
//        // Just make sure it passes the bag through expression builder
//        $task = new Task('Task');
//        $task->setExpressionBuilder(new MockCronExpression('1 * * * *')); // for testing
//
//        $this->assertEquals(new CronExpression('1 * * * *'), $task->getCronExpression(), 'failed to build an expression with the builder');
//    }

    // Passed to CronExpression, not tested here: isDue(), getNextRunDate(), getPreviousRunDate()
}

