<?php namespace Mistletoe\Test;

use Mistletoe\TaskPlanner;

class TaskPlannerIntegrationTest extends ScenarioSetups
{
    /** @test */
    public function TestSimpleExpression()
    {
        $planner = new TaskPlanner();
        $planner->add('TaskOne')->schedule('0 4,12,18 * * *');

        $this->assertEquals('0 4,12,18 * * *', (string)$planner->getTask('TaskOne')->getCronExpression(), "failed to set a simple expression");
    }

    /** @test */
    public function TestSimpleIntervals()
    {
        $planner = new TaskPlanner();
        $planner->add('TaskYearly')->yearly();
        $planner->add('TaskAnnually')->annually();
        $planner->add('TaskMonthly')->monthly();
        $planner->add('TaskWeekly')->weekly();
        $planner->add('TaskDaily')->daily();
        $planner->add('TaskHourly')->hourly();

        $this->assertEquals('0 0 1 1 *', (string)$planner->getTask('TaskYearly')->getCronExpression(), "failed to set a simple expression");
        $this->assertEquals('0 0 1 1 *', (string)$planner->getTask('TaskAnnually')->getCronExpression(), "failed to set a simple expression");
        $this->assertEquals('0 0 1 * *', (string)$planner->getTask('TaskMonthly')->getCronExpression(), "failed to set a simple expression");
        $this->assertEquals('0 0 * * 0', (string)$planner->getTask('TaskWeekly')->getCronExpression(), "failed to set a simple expression");
        $this->assertEquals('0 0 * * *', (string)$planner->getTask('TaskDaily')->getCronExpression(), "failed to set a simple expression");
        $this->assertEquals('0 * * * *', (string)$planner->getTask('TaskHourly')->getCronExpression(), "failed to set a simple expression");
    }

    /** @test */
    public function TestDailyIntervalsAtTime()
    {
        $planner = new TaskPlanner();

        $planner->add('TaskDailyOne')->daily()->at('1:30');
        $this->assertEquals('30 1 * * *', (string)$planner->getTask('TaskDailyOne')->getCronExpression(), "failed to set a simple expression");

        $planner->add('TaskDailyTwo')->daily()->atMidnight();
        $this->assertEquals('00 24 * * *', (string)$planner->getTask('TaskDailyTwo')->getCronExpression(), "failed to set a simple expression");

        $planner->add('TaskDailyThree')->at('7:49');
        $this->assertEquals('49 7 * * *', (string)$planner->getTask('TaskDailyThree')->getCronExpression(), "failed to set a simple expression");
    }

    /** @test */
    public function TestMonthlyIntervals()
    {
        $planner = new TaskPlanner();

        $planner->add('TaskOne')->monthly()->onDay(6)->at('1:30');
        $this->assertEquals('30 1 6 * *', (string)$planner->getTask('TaskOne')->getCronExpression(), "failed to set a simple expression");

        $planner->add('TaskTwo')->on('9-14')->andOn('10/31');
        $this->assertEquals('0 0 14,31 9,10 *', (string)$planner->getTask('TaskTwo')->getCronExpression(), "failed to set a simple expression");

        $planner->add('TaskThree')->on('11-12')->andOnDay(5)->andOnMonth(9)->at('7:49');
        $this->assertEquals('49 7 12,5 11,9 *', (string)$planner->getTask('TaskThree')->getCronExpression(), "failed to set a simple expression");
    }

    /** @test */
    public function TestWeekdays()
    {
        $planner = new TaskPlanner();
        $planner->add('Task')
            ->onSaturday()->onSunday()
            ->at('13:14');

        $this->assertEquals('14 13 * * 6,0', (string)$planner->getTask('Task')->getCronExpression(), "failed to set a simple expression");
    }

    /** @test */
    public function TestComplexScenarios()
    {
        /* Complex Scenario: “At every 30 and 59th minute past the 1, 4 and 8th hour on the 2, 4, 9 and 10th in Jan, Mar and Sep.” */
        $expected = '30,59 1,4,8 2,4,9 1,3,9 *';

        $planner = new TaskPlanner();
        $planner->add('Task')
            ->atMinute(30)
            ->andAtMinute(59)
            ->atHour([1,4,8])
            ->onDay(2)
            ->andOnDay([4,9])
            ->onMonth([1,3,9]);

        $this->assertEquals($expected, (string)$planner->getTask('Task')->getCronExpression(), "failed to set a complex expression");

        /* Complex Scenario: “Every month on the 1st and 15th at 12:30 and 24:30” */
        $expected = '30 12,24 1,15 * *';

        $planner = new TaskPlanner();
        $planner->add('Task')
            ->monthly()
            ->at('12:30')
            ->andAtHour(24)
            ->onDay([1,15]);

        $this->assertEquals($expected, (string)$planner->getTask('Task')->getCronExpression(), "failed to set a complex expression");
    }
}

