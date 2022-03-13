<?php namespace ElectricJones\Mistletoe\Test\Unit;

/* The Getter and Setter methods are all tested in `TaskPlannerTest`. No need to duplicate */

use Cron\CronExpression;
use ElectricJones\Mistletoe\Task;
use ElectricJones\Mistletoe\Test\Mocks\MockExpressionBuilder;
use PHPUnit\Framework\TestCase;

class TaskBagTest extends TestCase
{
    /** @test */
    public function TestConstructNewBagWithName()
    {
        $task = new Task('Task');
        $this->assertEquals('Task', $task->getTask(), 'failed to set task name');
    }

    /* Appending Values */
    /** @test */
    public function TestAddMonth()
    {
        $task = new Task('Task');
        $task->addMonth(1)->addMonth(2)->addMonth(3);

        $this->assertEquals('1,2,3', $task->getMonth(), 'failed to append months');
    }

    /** @test */
    public function TestAddDay()
    {
        $task = new Task('Task');
        $task->addDay(1)->addDay(2)->addDay(3);

        $this->assertEquals('1,2,3', $task->getDay(), 'failed to append day');
    }

    /** @test */
    public function TestAddHour()
    {
        $task = new Task('Task');
        $task->addHour(1)->addHour(2)->addHour(3);

        $this->assertEquals('1,2,3', $task->getHour(), 'failed to append hours');
    }

    /** @test */
    public function TestAddMintue()
    {
        $task = new Task('Task');
        $task->addMinute(1)->addMinute(2)->addMinute(3);

        $this->assertEquals('1,2,3', $task->getMinute(), 'failed to append minutes');
    }

    /** @test */
    public function TestConstructNewBagWithParameters()
    {
        $task = new Task([
            'task'         => 'name',
            'environments' => ['env'],
            'followedBy'   => ['one', 'two'],
            'interval'     => 'int',
            'hour'         => 'hour',
            'minute'       => 'minute',
            'month'        => 'month',
            'day'          => 'day'
        ]);

        $this->assertEquals('name', $task->getTask(), 'failed to set task name');
        $this->assertEquals(['env'], $task->getEnvironments(), 'failed to set environments');
        $this->assertEquals(['one', 'two'], $task->getFollowedBy(), 'failed to set followed by');
        $this->assertEquals('int', $task->getInterval(), 'failed to set interval');
        $this->assertEquals('hour', $task->getHour(), 'failed to set hour');
        $this->assertEquals('minute', $task->getMinute(), 'failed to set minute');
        $this->assertEquals('month', $task->getMonth(), 'failed to set month');
        $this->assertEquals('day', $task->getDay(), 'failed to set day');
    }

    /** @test */
    public function TestSetCronExpression()
    {
        // From a string expression
        $task = new Task('Task');
        $task->setCronExpression('1 1 1 1 1');
        $this->assertEquals(new CronExpression('1 1 1 1 1'), $task->getCronExpression(), 'failed to set cron expression from string');

        // From a CronExpression instance
        $task = new Task('Task');
        $expression = new CronExpression('1 2 3 4 5');
        $task->setCronExpression($expression);
        $this->assertEquals(new CronExpression('1 2 3 4 5'), $task->getCronExpression(), 'failed to set cron expression from instance');

    }

    /** @test */
    // This builds a CronExpression object from a full bag. The ExpressionBuilder is mocked
    public function TestGetCronExpression()
    {
        // Just make sure it passes the bag through expression builder
        $task = new Task('Task');
        $task->setExpressionBuilder(new MockExpressionBuilder('1 * * * *')); // for testing

        $this->assertEquals(new CronExpression('1 * * * *'), $task->getCronExpression(), 'failed to build an expression with the builder');
    }

    // Passed to CronExpression, not tested here: isDue(), getNextRunDate(), getPreviousRunDate()
}

