<?php namespace ElectricJones\Mistletoe\Test;

use ElectricJones\Mistletoe\Command;
use ElectricJones\Mistletoe\TaskPlanner;
use ElectricJones\Mistletoe\Test\Mocks\MockTask1;
use PHPUnit\Framework\TestCase;


/**
 * Class ScenarioSetups
 * @package system\Mistletoe
 */
class ScenarioSetups extends TestCase
{
    /**
     * @var string The mistletoe/tests director
     */
    protected $testDir;

    public function setUp(): void
    {
        $this->testDir = __DIR__;
    }

    /**
     * @return string
     */
    public function getTestDir()
    {
        return $this->testDir;
    }

    /**
     * @return TaskPlanner
     */
    protected function getFullScenario()
    {
        $planner = new TaskPlanner(); // defaults to production environment
        $planner->setCurrentTime('01:30 2016-3-4'); // We will pretend like its 1:30 on March 4th

        $planner->add(MockTask1::class)->followedBy(Mocks\MockTask2::class)
            ->atMinute(30)
            ->andAtMinute(59)
            ->atHour([1, 4, 8])
            ->onDay(2)
            ->andOnDay([4, 9])
            ->onMonth([1, 3, 9]);

        $planner->add(Mocks\MockTask3::class)->daily()->at('1:30');

        $planner->add(Mocks\MockTask4::class)->always();

        $planner->add(Mocks\MockTask5::class)->hourly()->atMinute('30')->followedBy(new Command('sleep 1'))->followedBy(Mocks\MockTask6::class);

        $planner->add('NoTask')->schedule('* * * * *')->onDevelopmentOnly(); // should not run

        $planner->add('DeadTask')->monthly()->onDay(7)->at('1:30'); // Also should not run

        return $planner;
    }
}
